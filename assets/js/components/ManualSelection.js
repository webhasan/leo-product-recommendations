import DebounceInput from "./DebounceInput";
import { TreeSelect } from "@wordpress/components";
import buildTermsTree from "../functions/tree";
import {
    DndContext,
    closestCenter,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
} from "@dnd-kit/core";
import {
    SortableContext,
    arrayMove,
    sortableKeyboardCoordinates,
    useSortable,
    verticalListSortingStrategy,
} from "@dnd-kit/sortable";
import { CSS } from "@dnd-kit/utilities";
import classNames from "classnames";

const __ = wp.i18n.__;

/**
 * One selected product: draggable with mouse, touch and keyboard (space + arrows).
 * The hidden input keeps the saved order (products are posted in DOM order).
 */
const SortableProduct = ({ product, onRemoveProduct }) => {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({ id: product.id });

    return (
        <li
            ref={setNodeRef}
            style={{
                transform: CSS.Transform.toString(transform),
                transition,
            }}
            className={classNames({ dragged: isDragging })}
            {...attributes}
            {...listeners}
        >
            <input
                type="hidden"
                name="_lc_lpr_data[products][]"
                value={product.id}
            />
            <span className="single-list">
                <div className="thumb">
                    <img
                        src={!!product.feature_image ? product.feature_image : ""}
                        alt=""
                    />
                </div>
                {product.title}
                <span
                    className="remove-item"
                    role="button"
                    tabIndex={0}
                    aria-label={__("Remove", "leo-product-recommendations")}
                    onPointerDown={(e) => e.stopPropagation()}
                    onKeyDown={(e) => {
                        e.stopPropagation();
                        if (e.key === "Enter" || e.key === " ") {
                            e.preventDefault();
                            onRemoveProduct(product.id);
                        }
                    }}
                    onClick={(e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        onRemoveProduct(product.id);
                    }}
                >
                    -
                </span>
            </span>
        </li>
    );
};

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

    // Small move threshold so a click on "-" or a product is not treated as a drag.
    const sensors = useSensors(
        useSensor(PointerSensor, { activationConstraint: { distance: 4 } }),
        useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates })
    );

    const handleDragEnd = ({ active, over }) => {
        if (!over || active.id === over.id) {
            return;
        }
        const from = initialProducts.findIndex((product) => product.id === active.id);
        const to = initialProducts.findIndex((product) => product.id === over.id);
        if (from !== -1 && to !== -1) {
            onReorder(arrayMove(initialProducts, from, to));
        }
    };

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
                                        "No products found",
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
                                    role="button"
                                    tabIndex={0}
                                    aria-pressed={!!product.selected}
                                    onClick={() => onAddProduct(product) }
                                    onKeyDown={(e) => {
                                        if (e.key === "Enter" || e.key === " ") {
                                            e.preventDefault();
                                            onAddProduct(product);
                                        }
                                    }}
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
                    <DndContext
                        sensors={sensors}
                        collisionDetection={closestCenter}
                        onDragEnd={handleDragEnd}
                    >
                        <SortableContext
                            items={initialProducts.map((product) => product.id)}
                            strategy={verticalListSortingStrategy}
                        >
                            <ul>
                                {initialProducts.map((product) => (
                                    <SortableProduct
                                        key={product.id}
                                        product={product}
                                        onRemoveProduct={onRemoveProduct}
                                    />
                                ))}
                            </ul>
                        </SortableContext>
                    </DndContext>
                </div>

                
            </div>
            {/* end .product-selection */}
        </div>
        {/* end .product-selection-panel */}
    </>
}

export default ManualSelection;