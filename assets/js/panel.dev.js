import Reorder from 'react-reorder';

(function(React, __, $, app, Reorder) {

	if(!app) return;

	const LoadingIcon = () => (
		<svg version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 100 100" enable-background="new 0 0 100 100" width="80" height="80">
		<rect fill="#0073aa" width="3" height="45.2018" transform="translate(0) rotate(180 3 50)">
			<animate attributeName="height" attributeType="XML" dur="1s" values="30; 100; 30" repeatCount="indefinite"></animate>
		</rect>
		<rect x="17" fill="#0073aa" width="3" height="31.2018" transform="translate(0) rotate(180 20 50)">
			<animate attributeName="height" attributeType="XML" dur="1s" values="30; 100; 30" repeatCount="indefinite" begin="0.1s"></animate>
		</rect>
		<rect x="40" fill="#0073aa" width="3" height="56.7982" transform="translate(0) rotate(180 40 50)">
			<animate attributeName="height" attributeType="XML" dur="1s" values="30; 100; 30" repeatCount="indefinite" begin="0.3s"></animate>
		</rect>
		<rect x="60" fill="#0073aa" width="3" height="84.7982" transform="translate(0) rotate(180 58 50)">
			<animate attributeName="height" attributeType="XML" dur="1s" values="30; 100; 30" repeatCount="indefinite" begin="0.5s"></animate>
		</rect>
		<rect x="80" fill="#0073aa" width="3" height="31.2018" transform="translate(0) rotate(180 76 50)">
			<animate attributeName="height" attributeType="XML" dur="1s" values="30; 100; 30" repeatCount="indefinite" begin="0.1s"></animate>
		</rect>
		</svg>
	);

	// const ThumbnailPlacehlder = 

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

			// return false;
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
					console.log(data);

					let {products, selectedProducts, heading} = data;

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
				{!facedDate && <span className="pgfy-recommend-product-prelaoder">{<LoadingIcon />}</span> }
				
				<div className="recommend-prodcut-options-wrap" style = {opacity}>
					<div className="pr-field">
						<div className="rp-panel-title">{__('Recommend Product Heading','woocommerce-product-recommend')}</div>
						<p><input type="text" name="pgfy_pr_data[heading]" value = {heading} onChange = {onChangeHeading}/></p>
					</div>

					<div className="pr-field">
							<div className="rp-panel-title">{__('Select Product')}</div>
							<div className="product-selection-panel">
							<div className="product-filter">
								<div className="search">
									<input type="text" onChange = {onChangeQuery} placeholder= {__('Search...','woocommerce-product-recommend')} value = {query}/>
								</div>
								<div className="category-filter">
									<select name="category" onChange = {onChangeCat} value = {selectedCategory}>
										<option value="">{__('Select Category','woocommerce-product-recommend')}</option>
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
														<img src={!!product.thumbnail_image ? product.thumbnail_image : ''} alt="" />
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
												<img src={!!product.thumbnail_image ? product.thumbnail_image : ''} alt="" />
												</div>
												{product.title}
												<span  className="remove-item" 
												onMouseDown = {(e) => {
													e.preventDefault();
													e.stopPropagation();
													removeProduct(product.id)
												}}
												onClick = { (e) => {
													e.stopPropagation();
													e.preventDefault();
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

	React.render(<SelectProduct/>, app)
})(wp.element, wp.i18n.__, jQuery, document.getElementById('pr-app'), Reorder);
  