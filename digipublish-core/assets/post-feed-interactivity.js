import {
	getContext,
	getElement,
	store,
	withScope,
} from '@wordpress/interactivity';

const namespace = 'digipublish/post-feed';

function postIds( section ) {
	return Array.from( section.querySelectorAll( '.tp-card[data-post-id]' ) )
		.map( ( card ) => parseInt( card.getAttribute( 'data-post-id' ), 10 ) || 0 )
		.filter( Boolean );
}

const { actions } = store( namespace, {
	actions: {
		*loadNext() {
			const context = getContext();
			const { ref } = getElement();

			if (
				context.isLoading ||
				context.ended ||
				context.page >= context.maxPages
			) {
				return;
			}

			const section = ref?.closest( '.tp-post-feed' );
			const track = section?.querySelector( '.tp-feed' );
			if ( ! section || ! track || ! context.restUrl ) {
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
						exclude: postIds( section ),
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
	},
} );
