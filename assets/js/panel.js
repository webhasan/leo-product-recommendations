import fetchProducts from './functions/ajax/fetchProducts';
import fetchCategories from './functions/ajax/fetchCategories';
import fetchAppData from './functions/ajax/fetchAppData';
import LoadingIcon from './components/Loading';
import RecommendationHeading from './components/RecommendationHeading';
import RadioSelection from './components/RadioSelection';
import ManualSelection from './components/ManualSelection';

(function (React, __, app) {
  if (!app) return;

  //Get Product Id
  const postId = parseInt(app.getAttribute("data-id"));

  //Get data from localized script
  const { ajax_url: apiEndPoint, nonce, pro_image, pro_link } = lc_pr_panel_data;

  //React Sate and Effect Hook
  const { useState, useEffect } = React;

  // Heading Type
  const headingType = [
    {
      id: "heading",
      title: __("Heading", "leo-product-recommendations"),
    },
    {
      id: "article",
      title: __("Heading & Description", "leo-product-recommendations"),
    },
  ];

  // Recommendations product select options
  const selectionMethods = [
    {
      id: "manual-selection",
      title: __("Manual Selection", "leo-product-recommendations"),
    },
    {
      id: "dynamic-selection",
      title: __("Dynamic Selection", "leo-product-recommendations"),
    },
  ];

  // free version only support manual selection
  const type = "manual-selection";

  const SelectProduct = () => {
    const [appData, setAppData] = useState({
      heading: "",
      heading_article: "",
      heading_type: "heading",
      selectMethod: "manual-selection",
      products: [],
    });
    const [loadedData, setLoadedData] = useState(false);
    const [fetchingProducts, setFetchingProducts] = useState(true);
    const [categories, setCategories] = useState([]);
    const [products, setProducts] = useState([]);
    const [page, setPage] = useState(1);
    const [maxPage, setMaxPage] = useState(1);
    const [selectedCategory, setSelectedCategory] = useState("");
    const [searchTerm, setSearchTerm] = useState("");


    // add product in recommendations list
    const addProduct = (product) => {
      let products = [product, ...appData.products];
      setAppData({ ...appData, products });
    };

    // remove product from recommendations list
    const removeProduct = (id) => {
      let products = appData.products.filter(
        (product) => product.id !== id
      );
      setAppData({ ...appData, products });
    };

    // query products form next page when scroll to the last product
    const handleScroll = (event) => {
      if (!fetchingProducts) {
        const bottom =
          event.target.scrollHeight - event.target.scrollTop <=
          event.target.clientHeight + 1;

        if (bottom && page < maxPage) {
          setPage(page + 1);
        }
      }
    };

    // check product is selectable, if product is already added in recommendation list
    // then it will not selectable
    const selectable = (products) => {
      return products.map((product) => {
        let isSelected = appData.products.find(
          (selectedProduct) => selectedProduct.id === product.id
        );

        if (isSelected) {
          product["selected"] = true;
        } else {
          product["selected"] = false;
        }

        return product;
      });
    };

    // set opacity 0 when initial data is loading
    const opacity = {
      opacity: loadedData ? 1 : 0,
    };

    useEffect(() => {
    // loading initial app data
    fetchAppData(apiEndPoint, nonce, postId).then(data => {
      if (data) {
        let products = data.products || [];
        let heading = data.heading || "";
        let heading_article = data.heading_article || "";
        let heading_type = data.heading_type || "heading";

        setAppData({
          ...appData,
          products,
          heading_type,
          heading,
          heading_article,
        });
      }
      setLoadedData(true);
    });
    }, []);

    // loading all categories of products
    useEffect(() => {
      fetchCategories(apiEndPoint, nonce).then(data => {
        if (data.length) {
          setCategories(data);
        }
      });
    }, []);

    // loading products for selection panel
    useEffect(() => {
      setFetchingProducts(true);

      // when back to page no: 1 make empty previous products list 
      // before fetching products
      if (page === 1) {
        setProducts([]);
      }

      fetchProducts(apiEndPoint, nonce, postId, page, selectedCategory, searchTerm).then(data => {
        if(data) {
          let { products: newProducts, max_page: maxPage } = data;
          if (page === 1) {
            setProducts(newProducts);
          } else {
            // setProducts([...products, ...newProducts]);
            setProducts(oldProducts => [...oldProducts, ...newProducts]);
          }

          setMaxPage(maxPage);
          setFetchingProducts(false);
        }
      });
    }, [page, selectedCategory, searchTerm]);

    return (
      <div className="lc-recommendation-product">
        <input type="hidden" value={nonce} name="lc_pr_panel_nonce" />
        <input type="hidden" name="_lc_lpr_data[type]" value={type} />
        {!loadedData && (
          <span className="lc-recommendation-product-loader">
            {<LoadingIcon />}
          </span>
        )}

        <div className="recommendation-product-options-wrap" style={opacity}>

        <div className="pr-field">
          <RecommendationHeading
            textDomain='leo-product-recommendations'
            heading='Popup Heading'
            headingTypes = {headingType}
            docURL = 'https://cutt.ly/Lk3hveN'
            initialType = {appData.heading_type}
            typeName = '_lc_lpr_data[heading_type]'
            headingFieldName = '_lc_lpr_data[heading]'
            headingArticleFieldName = '_lc_lpr_data[heading_article]'
            headingValue = {appData.heading}
            headingArticleValue = {appData.heading_article}
            onChangeType = {
              (value) => {
                setAppData({
                  ...appData,
                  heading_type: value,
                })
              }
            }
            onChangeValue = {
              (type, value) => {
                if(type === 'heading') {
                  setAppData({ ...appData, heading: value })
                }else if ('heading_article') {
                  setAppData({...appData, heading_article: value});
                }
              }
            }
          />
          </div>

          <div className="pr-field">
            <RadioSelection 
              title = 'Select By'
              textDomain='leo-product-recommendations'
              docURL = 'https://cutt.ly/pk3hBPH'
              options={selectionMethods} 
              value = {appData.selectMethod}
              onChange = {(value) => {
                setAppData({
                  ...appData,
                  selectMethod: value
                });
              }}
            />
          </div>

          {appData.selectMethod === 'manual-selection' &&      
            <div className="pr-field">
              <ManualSelection 
                title = {__('Select Products', 'leo-product-recommendations')}
                searchPlaceholder = {__("Search...", "leo-product-recommendations")}
                onSearchChange = {(value) => {
                      setSearchTerm(value);
                      setPage(1);
                }}
                allCategories = {categories}
                selectedCategory = {selectedCategory}
                onChangeCategory = {(value) => {
                  setSelectedCategory(value);
                  setPage(1);
                }}
                onScrollList = {handleScroll}
                fetchingProducts = {fetchingProducts}
                notFoundSelectableProduct = {!fetchingProducts && !selectable(products).length}
                selectableProducts = {!!products.length && selectable(products)}
                onAddProduct = {addProduct}
                onRemoveProduct = {removeProduct}
                initialProducts = {appData.products}
                onReorder = { (products) => setAppData({ ...appData, products }) }
              />
            </div>
          }

          {appData.selectMethod === 'dynamic-selection' &&  
            <div className="pr-field">  
              <div className="pro-dynamic-selection">
              <div><a href={pro_link} target="_blank">{__('Get Pro Version »','leo-product-recommendations')}</a></div>
                <div><img src={pro_image} alt=""/></div>
              </div>
            </div>
          }
        </div>
      </div>
    );
  };

  React.render(<SelectProduct />, app);
})(wp.element, wp.i18n.__, document.getElementById("pr-app"));
