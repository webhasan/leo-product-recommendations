import Reorder from 'react-reorder';

(function(React, $, app, Reorder) {

	if(!app) return;
	const postId = parseInt(app.getAttribute('data-id'));

	const { reorder	} = Reorder;

	const apiEndPoint = ajax_url; 

	const {useState, useEffect} = React;


	const SelectProduct = () => {

		const [facedDate, setFacedDate] = useState(false);

		const [heading, setHeading] = useState([]);
		const [products, setProducts] = useState([]);
		const [selectedProducts, setSelectedProducts] = useState([]);

		const [categories, setCategories] = useState([]);
		const [selectedCategory, setSelectedCategory] = useState('');

		const [query, setQuery] = useState('');

		const onChangeHeading = (e) => {
			setHeading(e.target.value);
		}

		const onChangeQuery = (e) => {
			setQuery(e.target.value);
		}

		const reOrderItem = (event, previousIndex, nextIndex) => {
			setSelectedProducts(reorder(selectedProducts, previousIndex, nextIndex));
		}

		const visible = () => {
			let visibleProduct = products.filter(product => !selectedProducts.find(item => item.id === product.id))
			let queryString = query.trim();

			const searchProduct = (product) => product.title.toLowerCase().indexOf(queryString.toLowerCase()) !== -1;
			const filterCategory = (prodcut) => prodcut.categories.includes(selectedCategory);

			if(queryString) {
				visibleProduct = visibleProduct.filter(product => searchProduct(product));
			}

			if(selectedCategory) {
				visibleProduct = visibleProduct.filter(product => filterCategory(product));
			}

			return visibleProduct;
		}

		const addProdcut = (id) => {
			let addedItem = products.find(product => product.id === id);
			setSelectedProducts([...selectedProducts, addedItem]);
		}

		const removeProduct = (id) => {
			let restProducst = selectedProducts.filter(product => product.id !== id);
			setSelectedProducts(restProducst);

			return false;
		}

		const mergeCategory = (products) => {
			 let _categories  = products.reduce((total, current) => {
				return [...total, ...current.categories];
			 }, []);

			return [...new Set(_categories)].sort();
		}

		const onChangeCat = (e) => {
			setSelectedCategory(e.target.value)
		}

		const opacity = {
			opacity: facedDate ? 1 : 0
		}

		useEffect(function() {
			$.ajax({
				url: apiEndPoint,
				method: 'POST',
				data: {
					action: 'pr_fetch',
					post_id: postId
				},
		
				success: function(data) {
					let {products, selectedProducts, heading} = JSON.parse(data);
					
					setHeading(heading);
					setProducts(products);
					setSelectedProducts(selectedProducts);
					setCategories(mergeCategory(products));

					setFacedDate(true);
				}
			});
		}, []);

		return (
			<div className="pgfy-recommend-product">
				{!facedDate && <span className="pgfy-recommend-product-prelaoder">Loading...</span> }
				
				<div className="recommend-prodcut-options-wrap" style = {opacity}>
					<div className="pr-field">
						<div className="rp-panel-title">Recommend Product Heading</div>
						<p><input type="text" name="pgfy_pr_data[heading]" value = {heading} onChange = {onChangeHeading}/></p>
					</div>

					<div className="pr-field">
							<div className="rp-panel-title">Select Product</div>
							<div className="product-selection-panel">
							<div className="product-filter">
								<div className="search">
									<input type="text" onChange = {onChangeQuery} placeholder="Search..." value = {query}/>
								</div>
								<div className="category-filter">
									<select name="category" onChange = {onChangeCat} value = {selectedCategory}>
										<option value="">Select Category</option>
										{categories.map(cateory => <option key={cateory} value={cateory}>{cateory}</option>)}
									</select>
								</div>
							</div>
						
							<div className="product-selection">
							<div className="list-panel">
								{!!products.length && 
									<ul>
										{visible(products).map(product => (
											<li key = {product.id} onClick = {() => addProdcut(product.id)}>
												<span className="single-list">
													<div className="thumb">
														<img src={product.thumbnail_image} alt={product.title} />
													</div>
													{product.title}
												</span>
											</li>
										))}

									</ul>
								}
							</div>

							<div className="select-item-panel">
								<Reorder
									component="ul"
									reorderId="my-list"
									placeholderClassName="placeholder"
									// lock="horizontal"
									holdTime={50}
									touchHoldTime={50}
									onReorder={reOrderItem}
									autoScroll={false}
									placeholder={
										<li className="custom-placeholder" />
									}
								>
									{selectedProducts.map(product => (
										<li>
											<input type="hidden" name = "pgfy_pr_data[products][]" value = {product.id} />
											<span className="single-list" data-id ="10">
												<div className="thumb">
												<img src={product.thumbnail_image} alt={product.title} />
												</div>
												{product.title}
												<span  className="remove-item" 
												onMouseDown = {(e) => {
													e.stopPropagation; 
													e.preventDefault; 
													removeProduct(product.id)
												}}
												onClick = { (e) => {
													e.stopPropagation; 
													e.preventDefault; 
												}}
												>-</span>
											</span>
										</li>
									))}
								</Reorder>
							</div>
							</div>
						</div>
					</div>
				</div>
			</div>									
		)
	}


	React.render(
		<SelectProduct/>, app
	)
})(wp.element, jQuery, document.getElementById('pr-app'), Reorder);