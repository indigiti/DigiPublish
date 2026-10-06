import {
	store,
	getContext,
	getElement,
	withScope,
} from '@wordpress/interactivity';

const timers = new WeakMap();
const frames = new WeakMap();
const observers = new WeakMap();
const reduceMotion =
	window.matchMedia &&
	window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

function blockRoot( element ) {
	return element && element.closest
		? element.closest( '.tp-post-feed' )
		: null;
}

function carouselRoot( element ) {
	const root = blockRoot( element );
	return root && root.classList.contains( 'tp-post-feed--carousel' )
		? root
		: null;
}

function trackFor( root ) {
	return root ? root.querySelector( '.tp-feed' ) : null;
}

function carouselTrackFor( root ) {
	return root ? root.querySelector( '[data-dp-post-carousel-track]' ) : null;
}

function cardsFor( root ) {
	const track = carouselTrackFor( root );
	return track ? Array.from( track.querySelectorAll( '.tp-card' ) ) : [];
}

function postIds( root ) {
	return Array.from(
		root.querySelectorAll( '.tp-card[data-post-id]' )
	)
		.map( ( card ) => parseInt( card.getAttribute( 'data-post-id' ), 10 ) || 0 )
		.filter( Boolean );
}

function paginationAttributes( root ) {
	try {
		return JSON.parse( root.getAttribute( 'data-dp-attributes' ) || '{}' );
	} catch ( error ) {
		return {};
	}
}

function normalizeIndex( context, total, index ) {
	if ( context.wrap ) {
		if ( index < 0 ) return total - 1;
		if ( index >= total ) return 0;
	}
	return Math.max( 0, Math.min( total - 1, index ) );
}

function updatePresentation( root, context, index ) {
	const cards = cardsFor( root );
	if ( ! cards.length ) return;

	const current = normalizeIndex( context, cards.length, index );
	context.current = current;

	root.querySelectorAll( '[data-dp-carousel-dot]' ).forEach( ( dot, dotIndex ) => {
		const active = dotIndex === current;
		dot.classList.toggle( 'is-active', active );
		dot.setAttribute( 'aria-selected', active ? 'true' : 'false' );
	} );

	const prev = root.querySelector( '[data-dp-carousel-prev]' );
	const next = root.querySelector( '[data-dp-carousel-next]' );
	if ( ! context.wrap ) {
		if ( prev ) prev.disabled = current === 0;
		if ( next ) next.disabled = current === cards.length - 1;
	} else {
		if ( prev ) prev.disabled = false;
		if ( next ) next.disabled = false;
	}
}

function moveTo( root, context, index ) {
	const track = carouselTrackFor( root );
	const cards = cardsFor( root );
	if ( ! track || ! cards.length ) return;

	const current = normalizeIndex( context, cards.length, index );
	const card = cards[ current ];
	if ( ! card ) return;

	track.scrollTo( {
		left: card.offsetLeft - track.offsetLeft,
		behavior: reduceMotion ? 'auto' : 'smooth',
	} );
	updatePresentation( root, context, current );
}

function stopAutoplay( root ) {
	const timer = timers.get( root );
	if ( timer ) {
		window.clearInterval( timer );
		timers.delete( root );
	}
}

function startAutoplay( root, context, nextAction ) {
	if ( ! root || ! context.autoplay || reduceMotion || timers.has( root ) ) {
		return;
	}

	const scopedNext = withScope( nextAction );
	timers.set(
		root,
		window.setInterval( () => scopedNext(), 5000 )
	);
}

function stopObserver( sentinel ) {
	const observer = observers.get( sentinel );
	if ( observer ) {
		observer.disconnect();
		observers.delete( sentinel );
	}
}

const { actions } = store( 'digipublish/post-feed', {
	state: {
		get currentDisplay() {
			return getContext().current + 1;
		},
	},
	actions: {
		previous() {
			const context = getContext();
			const { ref } = getElement();
			const root = carouselRoot( ref );
			moveTo( root, context, context.current - 1 );
		},
		next() {
			const context = getContext();
			const { ref } = getElement();
			const root = carouselRoot( ref );
			moveTo( root, context, context.current + 1 );
		},
		goTo( event ) {
			const context = getContext();
			const { ref } = getElement();
			const root = carouselRoot( ref );
			const target = parseInt(
				event.currentTarget.getAttribute( 'data-dp-carousel-dot' ) || '0',
				10
			);
			moveTo( root, context, Number.isNaN( target ) ? 0 : target );
		},
		pauseAutoplay() {
			const { ref } = getElement();
			stopAutoplay( carouselRoot( ref ) );
		},
		resumeAutoplay() {
			const context = getContext();
			const { ref } = getElement();
			const root = carouselRoot( ref );
			startAutoplay( root, context, actions.next );
		},
		*loadNext() {
			const context = getContext();
			const { ref } = getElement();
			const root = blockRoot( ref ) || ref;
			if (
				! root ||
				context.loading ||
				context.ended ||
				context.page >= context.maxPages
			) {
				return;
			}

			const track = trackFor( root );
			const endpoint = root.getAttribute( 'data-dp-rest-url' );
			const attrs = paginationAttributes( root );
			if ( ! track || ! endpoint ) return;

			context.loading = true;
			context.status = 'Loading…';

			try {
				const response = yield fetch( endpoint, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify( {
						page: context.page + 1,
						attributes: attrs,
						exclude: postIds( root ),
						relatedPostId:
							parseInt( attrs._relatedPostId || 0, 10 ) || 0,
					} ),
				} );

				if ( ! response.ok ) {
					throw new Error( 'HTTP ' + response.status );
				}

				const data = yield response.json();
				if ( data && data.content ) {
					track.insertAdjacentHTML( 'beforeend', data.content );
				}

				const nextPage =
					data && data.page
						? parseInt( data.page, 10 )
						: context.page + 1;
				context.page = nextPage;
				root.setAttribute( 'data-dp-page', String( nextPage ) );

				context.ended =
					! data ||
					data.postsEnd ||
					nextPage >= context.maxPages ||
					! data.content;
				context.status = '';
			} catch ( error ) {
				context.status = 'Could not load more posts.';
			} finally {
				context.loading = false;
			}
		},
	},
	callbacks: {
		init() {
			const context = getContext();
			const { ref } = getElement();
			const root = blockRoot( ref ) || ref;
			if ( ! root ) return;

			if ( root.classList.contains( 'tp-post-feed--carousel' ) ) {
				updatePresentation( root, context, context.current || 0 );
				startAutoplay( root, context, actions.next );
			}

			return () => {
				stopAutoplay( root );
				const frame = frames.get( root );
				if ( frame ) window.cancelAnimationFrame( frame );
				frames.delete( root );
			};
		},
		syncFromScroll() {
			const context = getContext();
			const { ref: track } = getElement();
			const root = carouselRoot( track );
			if ( ! root || ! track || frames.has( root ) ) return;

			frames.set(
				root,
				window.requestAnimationFrame( () => {
					frames.delete( root );
					const cards = cardsFor( root );
					if ( ! cards.length ) return;

					const scrollLeft = Math.abs( track.scrollLeft );
					let best = 0;
					let distance = Infinity;
					cards.forEach( ( card, index ) => {
						const nextDistance = Math.abs(
							card.offsetLeft - track.offsetLeft - scrollLeft
						);
						if ( nextDistance < distance ) {
							distance = nextDistance;
							best = index;
						}
					} );
					updatePresentation( root, context, best );
				} )
			);
		},
		observeInfinite() {
			const context = getContext();
			const { ref: sentinel } = getElement();
			if ( ! sentinel || observers.has( sentinel ) ) return;

			if ( ! ( 'IntersectionObserver' in window ) ) {
				sentinel.hidden = true;
				return;
			}

			const scopedLoad = withScope( actions.loadNext );
			const observer = new IntersectionObserver(
				( entries ) => {
					entries.forEach( ( entry ) => {
						if (
							entry.isIntersecting &&
							! context.ended &&
							! context.loading
						) {
							scopedLoad();
						}
					} );
				},
				{ rootMargin: '600px 0px' }
			);

			observers.set( sentinel, observer );
			observer.observe( sentinel );

			return () => stopObserver( sentinel );
		},
	},
} );
