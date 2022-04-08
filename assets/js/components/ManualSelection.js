import { DebounceInput } from "react-debounce-input";
import { TreeSelect } from "@wordpress/components";
import buildTermsTree from "../functions/tree";
import Reorder, {reorder} from "react-reorder";
import classNames from "classnames";

const __ = wp.i18n.__;

const ManualSelection = (props) => {
    const { 
        title, 
        searchPlaceholder, 
        onSearchChange, 
        selectedCategory, 
        onChangeCategory, 
        allCategories, 
        onScrollList, 
        notFoundSelectableProduct,
        selectableProducts,
        onAddProduct,
        onRemoveProduct,
        fetchingProducts,
        initialProducts,
        onReorder
    } = props;

    return <>
        <div className="rp-panel-title">
            {title}
        </div>

        <div className="product-selection-panel">
            <div className="product-filter">
                <div className="search">
                    <DebounceInput
                        minLength={2}
                        debounceTimeout={300}
                        onChange={(event) => onSearchChange(event.target.value)}
                        placeholder={searchPlaceholder}
                    />
                </div>
                {/* end .search */}

                <div className="category-filter">
                    <TreeSelect
                        // label="All Category"
                        noOptionLabel={__(
                            "All Categories",
                            "leo-product-recommendations"
                        )}
                        onChange={(value) => onChangeCategory(value)}
                        selectedId={selectedCategory}
                        tree={buildTermsTree(allCategories)}
                    />
                </div>
                {/* end .category-filter */}
            </div>
            {/* end .product-filter */}

            <div className="product-selection">
                <div className="list-panel">
                    <ul onScroll={(event) => onScrollList(event)}>
                        {notFoundSelectableProduct && (
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

                        {!!selectableProducts && selectableProducts.map((product) => (
                                <li
                                    key={product.id}
                                    className={classNames({
                                        "selected-product": product.selected,
                                    })}
                                    onClick={() => onAddProduct(product) }
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
                                    invisible: !fetchingProducts
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
                        onReorder={(event, previousIndex, nextIndex) => {
                            let reorderProducts = reorder(
                                initialProducts,
                                previousIndex,
                                nextIndex
                            );
                            onReorder(reorderProducts);
                        }}
                        autoScroll={false}
                        placeholder={<li className="custom-placeholder" />}
                    >
                        {initialProducts.map((product) => (
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
                                            onRemoveProduct(product.id);
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
            {/* end .product-selection */}
        </div>
        {/* end .product-selection-panel */}
    </>
}

export default ManualSelection;