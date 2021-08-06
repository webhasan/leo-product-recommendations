import Reorder from "react-reorder";
import buildTermsTree from "./functions/tree";
import { TreeSelect } from "@wordpress/components";
import { DebounceInput } from "react-debounce-input";
import classNames from "classnames";
import LoadingIcon from './components/Loading';
import RecommendationHeading from './components/RecommnedationHeading';
import RadioSelection from './components/RadioSelection';
import ProductSelection from './components/ProductSelection';

(function (React, __, $, app, Reorder) {
  if (!app) return;

  const postId = parseInt(app.getAttribute("data-id"));
  const { reorder } = Reorder;
  const { ajax_url: apiEndPoint, nonce, pro_image, pro_link } = lc_pr_panel_data;
  const { useState, useEffect } = React;

  const SelectProduct = () => {
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

    const selectionMehtods = [
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

    const [facedData, setFacedData] = useState(false);
    const [fetchingPosts, setFetchingPosts] = useState(true);

    const [initialData, setInitialData] = useState({
      heading: "",
      heading_article: "",
      heading_type: "heading",
      demoMethod: "manual-selection",
      products: [],
    });

    const [page, setPage] = useState(1);
    const [maxPage, setMaxPage] = useState(1);

    const [products, setProducts] = useState([]);

    const [categories, setCategories] = useState([]);
    const [selectedCategory, setSelectedCategory] = useState("");

    const [query, setQuery] = useState("");

    const reorderProduct = (event, previousIndex, nextIndex) => {
      let reorderProducts = reorder(
        initialData.products,
        previousIndex,
        nextIndex
      );
      setInitialData({ ...initialData, products: reorderProducts });
    };

    const addProdcut = (product) => {
      let products = [product, ...initialData.products];
      setInitialData({ ...initialData, products });
    };

    const removeProduct = (id) => {
      let existsProducs = initialData.products.filter(
        (product) => product.id !== id
      );
      setInitialData({ ...initialData, products: existsProducs });
    };

    const handleScroll = (event) => {
      if (!fetchingPosts) {
        const bottom =
          event.target.scrollHeight - event.target.scrollTop ===
          event.target.clientHeight;
        if (bottom && page < maxPage) {
          setPage(page + 1);
        }
      }
    };

    const selectAble = (producs) => {
      return producs.map((product) => {
        let isSelected = initialData.products.find(
          (selectedProduct) => selectedProduct.id === product.id
        );

        if (isSelected) {
          product["selcted"] = true;
        } else {
          product["selcted"] = false;
        }

        return product;
      });
    };

    const opacity = {
      opacity: facedData ? 1 : 0,
    };

    useEffect(() => {
      $.ajax({
        url: apiEndPoint,
        method: "GET",
        data: {
          action: "lpr_initial_data",
          nonce,
          post_id: postId,
        },

        success: function (data) {
          if (data) {
            let products = data.products ? data.products : [];
            let heading = data.heading ? data.heading : "";
            let heading_article = data.heading_article
              ? data.heading_article
              : "";
            let heading_type = data.heading_type
              ? data.heading_type
              : "heading";

            setInitialData({
              ...initialData,
              products,
              heading_type,
              heading,
              heading_article,
            });
          }

          setFacedData(true);
        },
      });
    }, []);

    useEffect(() => {
      $.ajax({
        url: apiEndPoint,
        method: "GET",
        data: {
          action: "lpr_fetch_categories",
          nonce,
        },
        success: function (data) {
          if (data.length) {
            setCategories(data);
          }
        },
      });
    }, []);

    useEffect(() => {
      setFetchingPosts(true);
      if (page === 1) {
        setProducts([]);
      }

      $.ajax({
        url: apiEndPoint,
        method: "GET",
        data: {
          action: "lpr_fetch_products",
          nonce,
          post_id: postId,
          page,
          category: selectedCategory,
          query,
        },
        success: function (data) {
          let { products: newProducts, max_page: maxPage } = data;

          if (page === 1) {
            setProducts(newProducts);
          } else {
            setProducts([...products, ...newProducts]);
          }
          setMaxPage(maxPage);
          setFetchingPosts(false);
        },
      });
    }, [page, selectedCategory, query]);

    return (
      <div className="lc-recommendation-product">
        <input type="hidden" value={nonce} name="lc_pr_panel_nonce" />
        <input type="hidden" name="_lc_lpr_data[type]" value={type} />
        {!facedData && (
          <span className="lc-recommendation-product-prelaoder">
            {<LoadingIcon />}
          </span>
        )}

        <div className="recommendation-prodcut-options-wrap" style={opacity}>

        <div className="pr-field">
          <RecommendationHeading
            textDomain='leo-product-recommendations'
            heading='Popup Heading'
            headingTypes = {headingType}
            docURL = 'https://cutt.ly/Lk3hveN'
            initialType = {initialData.heading_type}
            typeName = '_lc_lpr_data[heading_type]'
            headingFieldName = '_lc_lpr_data[heading]'
            headingArticleFieldName = '_lc_lpr_data[heading_article]'
            headingValue = {initialData.heading}
            headingArticleValue = {initialData.heading_article}
            onChangeType = {
              (value) => {
                setInitialData({
                  ...initialData,
                  heading_type: value,
                })
              }
            }
            onChangeValue = {
              (type, value) => {
                if(type === 'heading') {
                  setInitialData({ ...initialData, heading: value })
                }else if ('heading_article') {
                  setInitialData({...initialData, heading_article: value});
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
              options={selectionMehtods} 
              value = {initialData.demoMethod}
              onChange = {(value) => {
                setInitialData({
                  ...initialData,
                  demoMethod: value
                });
              }}
            />
          </div>

          {initialData.demoMethod === 'manual-selection' &&      
            <div className="pr-field">
              <ProductSelection 
                title = 'Select Products'
                textDomain = 'leo-product-recommendations'
                
              />
            </div>
          }

          {initialData.demoMethod === 'manual-selection' &&      
            <div className="pr-field">
              <div className="rp-panel-title">
                {__('Select Products', 'leo-product-recommendations')}
              </div>
              
              <div className="product-selection-panel">
                <div className="product-filter">
                  <div className="search">
                    <DebounceInput
                      minLength={2}
                      debounceTimeout={300}
                      onChange={(event) => {
                        setQuery(event.target.value);
                        setPage(1);
                      }}
                      placeholder={__("Search...", "leo-product-recommendations")}
                    />
                  </div>

                  <div className="category-filter">
                    <TreeSelect
                      // label="All Category"
                      noOptionLabel={__(
                        "All Categories",
                        "leo-product-recommendations"
                      )}
                      onChange={(value) => {
                        setSelectedCategory(value);
                        setPage(1);
                      }}
                      selectedId={selectedCategory}
                      tree={buildTermsTree(categories)}
                    />
                  </div>
                </div>

                <div className="product-selection">
                  <div className="list-panel">
                    <ul onScroll={handleScroll}>
                      {!fetchingPosts && !selectAble(products).length && (
                        <li className="disabled">
                          <span className="single-list">
                            {" "}
                            {__(
                              "Not found selectable product",
                              "leo-product-recommendations"
                            )}
                          </span>
                        </li>
                      )}

                      {!!products.length &&
                        selectAble(products).map((product) => (
                          <li
                            key={product.id}
                            className={classNames({
                              "selected-product": product.selcted,
                            })}
                            onClick={() => addProdcut(product)}
                          >
                            <span className="single-list">
                              <div className="thumb">
                                <img
                                  src={
                                    !!product.feature_image
                                      ? product.feature_image
                                      : ""
                                  }
                                  alt=""
                                />
                              </div>
                              {product.title}
                            </span>
                          </li>
                        ))}

                      {
                        <li
                          className={classNames("disabled", {
                            invisible: !fetchingPosts,
                          })}
                        >
                          <span className="lpr-loading-posts"></span>
                        </li>
                      }
                    </ul>
                  </div>

                  <div className="select-item-panel">
                    <Reorder
                      component="ul"
                      reorderId="my-list"
                      placeholderClassName="placeholder"
                      holdTime={50}
                      touchHoldTime={50}
                      onReorder={reorderProduct}
                      autoScroll={false}
                      placeholder={<li className="custom-placeholder" />}
                    >
                      {initialData.products.map((product) => (
                        <li key={product.id}>
                          <input
                            type="hidden"
                            name="_lc_lpr_data[products][]"
                            value={product.id}
                          />
                          <span className="single-list" data-id="10">
                            <div className="thumb">
                              <img
                                src={
                                  !!product.feature_image
                                    ? product.feature_image
                                    : ""
                                }
                                alt=""
                              />
                            </div>
                            {product.title}
                            <span
                              className="remove-item"
                              onMouseDown={(e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                removeProduct(product.id);
                              }}
                              onClick={(e) => {
                                e.stopPropagation();
                                e.preventDefault();
                              }}
                            >
                              -
                            </span>
                          </span>
                        </li>
                      ))}
                    </Reorder>
                  </div>
                </div>
              </div>
            </div>
          }
          {initialData.demoMethod === 'dynamic-selection' &&  
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
})(wp.element, wp.i18n.__, jQuery, document.getElementById("pr-app"), Reorder);
