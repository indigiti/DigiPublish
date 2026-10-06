import {
	store,
	getElement,
	watch,
} from '@wordpress/interactivity';

const schemeKey = 'digipublish-scheme';
const legacySchemeKey = 'digipublish-caards-scheme';
const root = document.documentElement;

const { state } = store( 'digipublish/site', {
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
