import WPEditor from "./WPEditor";
const { __ } = wp.i18n;

const RecommendationHeading = (props) => {
    const {
        textDomain,
        heading,
        headingTypes,
        docURL,
        initialType,
        typeName,
        headingFieldName,
        headingArticleFieldName,
        headingValue,
        headingArticleValue,
        onChangeType,
        onChangeValue
    } = props;

    return (
        <>
            <div className="rp-panel-title">
                {__(heading, textDomain)}
            </div>
            <div className="heading-control">
                {headingTypes.map((method) => (
                    <label key={method.id}>
                        <input
                            type="radio"
                            name={typeName}
                            value={method.id}
                            checked={initialType === method.id}
                            onChange={(e) => onChangeType(e.target.value)}
                        />
                        {method.title}
                    </label>
                ))}
            </div>
            <div
                style={{
                    display:
                        initialType === "article" ? "block" : "none",
                }}
            >
                <WPEditor
                    id="header-description"
                    name={
                        initialType === "article"
                            ? headingArticleFieldName
                            : ""
                    }
                    className="heading-article wp-editor-area"
                    value={headingArticleValue}
                    onChange={(value) => onChangeValue('heading_article', value)}
                />
            </div>

            <p
                className="heading-input"
                style={{
                    display:
                        initialType === "heading" ? "block" : "none",
                }}
            >
                <input
                    type="text"
                    name={
                        initialType === "heading"
                            ? headingFieldName
                            : ""
                    }
                    value={headingValue}
                    onChange={(e) => onChangeValue('heading', e.target.value)}
                />
            </p>

            {
                docURL &&
                <p><a href={docURL} target="_blank">{__('Documentation»', { textDomain })}</a></p>
            }

        </>
    );
}

export default RecommendationHeading;