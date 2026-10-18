/**
 * External dependencies
 */
/**
 * Group terms by a key (replacement for lodash groupBy).
 *
 * @param {Array}  items Terms.
 * @param {string} key   Property name.
 * @return {Object} Items grouped by String( item[ key ] ).
 */
const groupBy = ( items, key ) =>
	items.reduce( ( groups, item ) => {
		const group = String( item[ key ] );
		( groups[ group ] = groups[ group ] || [] ).push( item );
		return groups;
	}, {} );

/**
 * Returns terms in a tree form.
 *
 * @param {Array} flatTerms  Array of terms in flat format.
 *
 * @return {Array} Array of terms in tree format.
 */
export default function buildTermsTree( flatTerms ) {
	const flatTermsWithParentAndChildren = flatTerms.map( ( term ) => {
		return {
			children: [],
			parent: null,
			...term,
		};
	});

	const termsByParent = groupBy( flatTermsWithParentAndChildren, 'parent' );
	if ( termsByParent.null && termsByParent.null.length ) {
		return flatTermsWithParentAndChildren;
	}
	const fillWithChildren = ( terms ) => {
		return terms.map( ( term ) => {
			const children = termsByParent[ term.id ];
			return {
				...term,
				children:
					children && children.length
						? fillWithChildren( children )
						: [],
			};
		} );
	};

	return fillWithChildren( termsByParent[ '0' ] || [] );
}