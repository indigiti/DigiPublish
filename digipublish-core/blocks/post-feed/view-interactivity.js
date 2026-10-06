import {
	store,
	getContext,
	getElement,
	withScope,
} from '@wordpress/interactivity';

const timers = new WeakMap();
const frames = new WeakMap();
const reduceMotion =
	window.matchMedia &&
	window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

function carouselRoot( element ) {
	return element && element.closest
		? element.closest( '.tp-post-feed--carousel' )
		: null;
}

function trackFor( root ) {
	return root ? root.querySelector( '[data-dp-post-carousel-track]' ) : null;
}

function cardsFor( root ) {
	const track = trackFor( root );
	return track ? Array.from( track.querySelectorAll( '.tp-card' ) ) : [];
}

function postIds( root ) {
	return root
		? Array.from( root.querySelectorAll( '.tp-card[data-post-id]' ) )
				.map(
					( card ) =>
						parseInt( card.getAttribute( 'data-post-id' ), 10 ) || 0
				)
				.filter( Boolean )
		: [];
}

function feedRoot( element ) {
	return element && element.closest ? element.closest( '.tp-post-feed' ) : null;
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
	const track = trackFor( root );
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

const { actions } = store( 'digipublish/post-feed', {
	state: {
		get currentDisplay() {
			return getContext().current + 1;
		},
	},
	actions: {
		*loadNext() {
			const context = getContext();
			const { ref } = getElement();
			const root = feedRoot( ref );

			if (
				! root ||
				context.isLoading ||
				context.ended ||
				context.page >= context.maxPages ||
				! context.restUrl
			) {
				return;
			}

			const track = root.querySelector( '.tp-feed' );
			if ( ! track ) {
				return;
			}

			context.isLoading = true;
			context.status = 'Loading…';

			try {
				const response = yield fetch( context.restUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify( {
						page: context.page + 1,
						attributes: context.attributes || {},
						exclude: postIds( root ),
						relatedPostId:
							parseInt(
								context.attributes?._relatedPostId || 0,
								10
							) || 0,
					} ),
				} );

				if ( ! response.ok ) {
					throw new Error( 'HTTP ' + response.status );
				}

				const data = yield response.json();
				if ( data?.content ) {
					track.insertAdjacentHTML( 'beforeend', data.content );
				}

				const nextPage = data?.page
					? parseInt( data.page, 10 )
					: context.page + 1;

				context.page = nextPage;
				root.setAttribute( 'data-dp-page', String( nextPage ) );
				context.ended =
					! data ||
					Boolean( data.postsEnd ) ||
					nextPage >= context.maxPages ||
					! data.content;
				context.status = '';
			} catch ( error ) {
				context.status = 'Could not load more posts.';
			} finally {
				context.isLoading = false;
			}
		},
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
	},
	callbacks: {
		initInfinite() {
			const context = getContext();
			const { ref } = getElement();

			if (
				context.pagination !== 'infinite' ||
				! ref ||
				! ( 'IntersectionObserver' in window )
			) {
				return;
			}

			const observer = new IntersectionObserver(
				withScope( ( entries ) => {
					if (
						entries.some( ( entry ) => entry.isIntersecting ) &&
						! context.ended
					) {
						actions.loadNext();
					}
				} ),
				{ rootMargin: '600px 0px' }
			);

			observer.observe( ref );
			return () => observer.disconnect();
		},
		init() {
			const context = getContext();
			const { ref } = getElement();
			const root = carouselRoot( ref ) || ref;
			if ( ! root ) return;

			updatePresentation( root, context, context.current || 0 );
			startAutoplay( root, context, actions.next );

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
	},
} );
