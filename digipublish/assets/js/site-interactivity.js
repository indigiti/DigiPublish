import {
	store,
	getContext,
	getElement,
	watch,
	withScope,
} from '@wordpress/interactivity';

const schemeKey = 'digipublish-scheme';
const legacySchemeKey = 'digipublish-caards-scheme';
const root = document.documentElement;

function bindNextPostHistory( section ) {
	if (
		! section ||
		! section.dataset.url ||
		! ( 'IntersectionObserver' in window )
	) {
		return;
	}

	const observer = new IntersectionObserver(
		( entries ) => {
			entries.forEach( ( entry ) => {
				if ( entry.isIntersecting && entry.intersectionRatio > 0.35 ) {
					const title = section.getAttribute( 'data-title' ) || document.title;
					const url = section.getAttribute( 'data-url' );
					if ( url && window.location.href !== url ) {
						window.history.replaceState( { dpNextPost: true }, title, url );
						document.title = title;
					}
				}
			} );
		},
		{ threshold: [ 0.35, 0.6 ] }
	);
	observer.observe( section );
}

const { state, actions } = store( 'digipublish/site', {
	state: {
		searchOpen: false,
		menuOpen: false,
		dark: false,
		schemeReady: false,
		schemeLabel: 'Use dark mode',
		sticky: false,
	},
	actions: {
		toggleSearch() {
			state.searchOpen = ! state.searchOpen;
			if ( state.searchOpen ) {
				state.menuOpen = false;
			}
		},
		toggleMenu() {
			state.menuOpen = ! state.menuOpen;
			if ( state.menuOpen ) {
				state.searchOpen = false;
			}
		},
		closeOverlays() {
			state.searchOpen = false;
			state.menuOpen = false;
		},
		toggleScheme() {
			state.dark = ! state.dark;
			state.schemeReady = true;
			state.schemeLabel = state.dark ? 'Use light mode' : 'Use dark mode';
		},
		*loadNextPost() {
			const context = getContext();
			const { ref } = getElement();

			if (
				! ref ||
				context.isLoading ||
				context.ended ||
				! context.currentPostId ||
				! context.restUrl
			) {
				return;
			}

			context.isLoading = true;
			context.loadStatus = 'Loading next post…';

			try {
				const response = yield fetch( context.restUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify( {
						postId: context.currentPostId,
						exclude: context.loadedPostIds || [],
					} ),
				} );

				if ( ! response.ok ) {
					throw new Error( 'HTTP ' + response.status );
				}

				const data = yield response.json();
				if ( ! data || data.end || ! data.content ) {
					context.ended = true;
					context.loadStatus = '';
					return;
				}

				const wrap = document.createElement( 'div' );
				wrap.innerHTML = data.content;
				const section = wrap.firstElementChild;
				if ( section ) {
					ref.parentNode.insertBefore( section, ref );
					const nextId = parseInt( data.postId, 10 ) || 0;
					context.currentPostId = nextId;
					if (
						nextId &&
						! context.loadedPostIds.includes( nextId )
					) {
						context.loadedPostIds = [ ...context.loadedPostIds, nextId ];
					}
					bindNextPostHistory( section );
				}
				context.loadStatus = '';
			} catch ( error ) {
				context.loadStatus = 'Could not load the next post.';
			} finally {
				context.isLoading = false;
			}
		},
	},
	callbacks: {
		initShell() {
			if ( ! state.schemeReady ) {
				let saved = 'light';
				try {
					saved =
						window.localStorage.getItem( schemeKey ) ||
						window.localStorage.getItem( legacySchemeKey ) ||
						'light';
				} catch ( error ) {
					saved = 'light';
				}
				state.dark = saved === 'dark';
				state.schemeLabel = state.dark ? 'Use light mode' : 'Use dark mode';
				state.schemeReady = true;
			}
			state.sticky = window.scrollY > 18;
		},
		handleKeydown( event ) {
			if ( event.key === 'Escape' ) {
				state.searchOpen = false;
				state.menuOpen = false;
			}
		},
		syncSticky() {
			state.sticky = window.scrollY > 18;
		},
		focusSearch() {
			if ( ! state.searchOpen ) {
				return;
			}
			const { ref } = getElement();
			if ( ! ref ) {
				return;
			}
			window.requestAnimationFrame( () => {
				const input = ref.querySelector( 'input[type="search"]' );
				if ( input ) {
					input.focus();
				}
			} );
		},
		initLoadNext() {
			const context = getContext();
			const { ref } = getElement();
			if (
				! ref ||
				context.ended ||
				! ( 'IntersectionObserver' in window )
			) {
				return;
			}

			const scopedLoad = withScope( actions.loadNextPost );
			const observer = new IntersectionObserver(
				( entries ) => {
					if (
						entries.some( ( entry ) => entry.isIntersecting ) &&
						! context.ended
					) {
						scopedLoad();
					}
				},
				{ rootMargin: '900px 0px' }
			);
			observer.observe( ref );
			return () => observer.disconnect();
		},
	},
} );

watch( () => {
	const menuOpen = state.menuOpen;
	if ( document.body ) {
		document.body.classList.toggle( 'dp-menu-open', menuOpen );
		document.body.classList.toggle( 'dp-caards-menu-open', menuOpen );
	}
} );

watch( () => {
	if ( ! state.schemeReady ) {
		return;
	}

	const dark = state.dark;
	root.classList.toggle( 'dp-theme-dark', dark );
	root.classList.toggle( 'dp-caards-dark', dark );
	root.setAttribute( 'data-dp-scheme', dark ? 'dark' : 'light' );

	try {
		window.localStorage.setItem( schemeKey, dark ? 'dark' : 'light' );
	} catch ( error ) {
		// Storage may be unavailable in privacy-restricted browsing contexts.
	}
} );
