import Reorder from 'react-reorder';
import { buildTermsTree } from './panel/tree';
import { TreeSelect } from '@wordpress/components';

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

	const postId = parseInt(app.getAttribute('data-id'));

	const { reorder	} = Reorder;
	const apiEndPoint = ajax_url; 
	const {useState, useEffect} = React;


	const SelectProduct = () => {
		// free version only support menual selection
		const type = 'menual-selection';

		const [facedDate, setFacedDate] = useState(false);

		const [initialData, setInitialData] = useState({
			heading: '',
			products: []
		});

		const [page, setPage] = useState(1);

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

		const reorderProduct = (event, previousIndex, nextIndex) => {
			let reorderProducts = reorder(initialData.products, previousIndex, nextIndex);
			setInitialData({...initialData, products: reorderProducts});
		}

		const addProdcut = (product) => {
			let products = [product, ...initialData.products];
			setInitialData({...initialData, products});
		}

		const removeProduct = (id) => {
			let existsProducs = initialData.products.filter(product => product.id !== id);
			setInitialData({...initialData, products: existsProducs});
		}

		const selectAble = (producs) => {
			return producs.filter(product => {
				return !initialData.products.find(selectedProduct => selectedProduct.id === product.id );
			});
		};

		const opacity = {
			opacity: facedDate ? 1 : 0
		}

		useEffect(() => {
			$.ajax({
				url: apiEndPoint,
				method: 'GET',
				data: {
					action: 'wpr_initial_data',
					post_id: postId
				},

				success: function(data) {
					let products = data.products ? data.products : [];
					let heading = data.heading ? data.heading: '';

					setInitialData({...initialData, products, heading });
					setFacedDate(true);
				}
			});
		}, []);

		useEffect(() => {
			$.ajax({
				url: apiEndPoint,
				method: 'GET',
				data: {
					action: 'wpr_fetch_categores'
				},
				success: function(data) {
					if(data.length) {
						setCategories(data);
					}
				}
			});
		},[]);

		useEffect(() => {
			$.ajax({
				url: apiEndPoint,
				method: 'GET',
				data: {
					action: 'wpr_fetch_products',
					post_id: postId,
					page,
					category: selectedCategory,
					query
				},
				success: function(data) {
					setProducts(data);
				}
			});
		}, [page, selectedCategory, query]); 

		return (
			<div className="pgfy-recommend-product">
				{!facedDate && <span className="pgfy-recommend-product-prelaoder">{<LoadingIcon />}</span> }
				
				<div className="recommend-prodcut-options-wrap" style = {opacity}>
					<div className="pr-field">
						<input type="hidden" name="_pgfy_pr_data[type]" value = {type} />

						<div className="rp-panel-title">{__('Recommend Product Heading','woocommerce-product-recommend')}</div>
						<p><input type="text" name="_pgfy_pr_data[heading]" value = {initialData.heading} onChange = {(e) => setInitialData({...initialData, heading: e.target.value})}/></p>
					</div>

					<div className="pr-field">
							<div className="rp-panel-title">{__('Select Product')}</div>
							<div className="product-selection-panel">
							<div className="product-filter">
								<div className="search">
									<input type="text" onChange = {onChangeQuery} placeholder= {__('Search...','woocommerce-product-recommend')} value = {query}/>
								</div>

								<div className="category-filter">
									<TreeSelect
										// label="All Category"
										noOptionLabel="All Categories"
										onChange={ ( value ) => setSelectedCategory( value ) }
										selectedId={ selectedCategory }
										tree = {buildTermsTree(categories)}
									/>
								</div>

							</div>
						
							<div className="product-selection">
							<div className="list-panel">
								{!!products.length && 
									<ul>
										{selectAble(products).map(product => (
											<li key = {product.id} onClick = {() => addProdcut(product)}>
												<span className="single-list">
													<div className="thumb">
														<img src={!!product.feature_image ? product.feature_image : ''} alt="" />
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
									onReorder={reorderProduct}
									autoScroll={false}
									placeholder={
										<li className="custom-placeholder" />
									}
								>
									{initialData.products.map(product => (
										<li key={product.id}>
											<input type="hidden" name = "_pgfy_pr_data[products][]" value = {product.id} />
											<span className="single-list" data-id ="10">
												<div className="thumb">
												<img src={!!product.feature_image ? product.feature_image : ''} alt="" />
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
  