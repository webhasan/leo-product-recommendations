const RecommendationHeading = (props) => {

    return (
        <button type="button" onClick={() => props.onClick('some value')}>Click Me!</button>
    )

    return (
        <div className="pr-field">
            <div className="rp-panel-title">
                {__("Popup Heading", "leo-product-recommendations")}
            </div>

            <div className="heading-control">
                {headingType.map((method) => (
                    <label key={method.id}>
                        <input
                            type="radio"
                            name="_lc_lpr_data[heading_type]"
                            value={method.id}
                            checked={initialData.heading_type === method.id}
                            onChange={(e) =>
                                setInitialData({
                                    ...initialData,
                                    heading_type: e.target.value,
                                })
                            }
                        />
                        {method.title}
                    </label>
                ))}
            </div>
            <div
                style={{
                    display:
                        initialData.heading_type === "article" ? "block" : "none",
                }}
            >
                <WPEditor
                    id="header-description"
                    name={
                        initialData.heading_type === "article"
                            ? "_lc_lpr_data[heading_article]"
                            : ""
                    }
                    className="heading-article wp-editor-area"
                    value={initialData.heading_article}
                    onChange={(value) => {
                        setInitialData({
                            ...initialData,
                            heading_article: value,
                        });
                    }}
                />
            </div>

            <p
                className="heading-input"
                style={{
                    display:
                        initialData.heading_type === "heading" ? "block" : "none",
                }}
            >
                <input
                    type="text"
                    name={
                        initialData.heading_type === "heading"
                            ? "_lc_lpr_data[heading]"
                            : ""
                    }
                    value={initialData.heading}
                    onChange={(e) =>
                        setInitialData({ ...initialData, heading: e.target.value })
                    }
                />
            </p>
            <p><a href="https://cutt.ly/Lk3hveN" target="_blank">{__('Documentation»', 'leo-product-recommendations')}</a></p>
        </div>
    );
}

export default RecommendationHeading;