const { __ } = wp.i18n;
const RadioSelection = (pros) => {
    const {
        title,
        textDomain,
        docURL,
        options,
        value,
        onChange
    } = pros;
    
    return <>
        <div className="rp-panel-title">
            {__(title, textDomain)}
        </div>
        <div className="selection-methods">
            {options.map((method) => (
                <label key={method.id}>
                    <input
                        type="radio"
                        value={method.id}
                        checked={value === method.id}
                        onChange={(e) => onChange(e.target.value)}
                    />
                    {method.title}
                </label>
            ))}
        </div>
        {docURL &&
            <p><a href="https://cutt.ly/pk3hBPH" target="_blank">{__('Documentation»', 'leo-product-recommendations')}</a></p>
        }
    </>;
}

export default RadioSelection;