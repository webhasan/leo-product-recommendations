const { useState, useEffect } = React;
const app = document.getElementById('lpr-field-page_recommendations');
const initialData = app.getAttribute('data-value');
import WPEditor from "./functions/wpEditor";

const PageRecommendations = () => {

   const [data, setData] = useState(JSON.parse(initialData));

    //heading types
    const headingType = [
        {
            id: 'heading',
            name: 'Heading',
        },
        {
            id: 'heading_descriptons',
            name: 'Heading & Description',
        }
    ];

    //add new recommendations
    const newRecommendations = () => {
        let date = new Date();
        let id = date.getMilliseconds();
        let heading_type = 'heading';
        setData([...data, { title: 'New Recommendations', id, heading_type }])
    }

    // remove item
    const removeItem = (id) => {
        console.log([...data]);
        let updatedData = [...data].filter(singleDate => {
            return singleDate.id !== id;
        });
        setData(updatedData);
    }

    // change heading type 
    const changeHeadingType = (id, value) => {
        let updateDate = [...data].map((singleData) => {
            if(singleData.id === id) {
                singleData.heading_type = value;
            }
            return singleData;
        });
        setData(updateDate);
    }

    useEffect(() => {
        // use effect 

    }, []);

    return (
        <div>
            <div className="recommendations">
                {data.map(singleData => (
                    <div className="single-recommendations">
                        <button type="button" className="remove-item" onClick={() => removeItem(singleData.id)}>X</button>
                        <input type="hidden" name={`lc_lpr_settings[page_recommendations][${singleData.id}][id]`} value={singleData.id}/>
                        <div className="title">{singleData.title}</div>
                        <div className="recommendations-heading">
                            <div className="heading-type">
                                {headingType.map(
                                    type => <lable>
                                        <input
                                            type="radio"
                                            name={`lc_lpr_settings[page_recommendations][${singleData.id}][heading_type]`}
                                            value={type.id}
                                            checked={type.id === singleData.heading_type}
                                            onChange= {(e) => changeHeadingType(singleData.id, e.target.value)}
                                        />
                                        {type.name}
                                    </lable>
                                )}
                            </div>

                            <div className="heading-fields">
                                <div
                                    style={{
                                        display:
                                        singleData.heading_type === "heading_descriptons" ? "block" : "none",
                                    }}
                                >
                                    <WPEditor
                                        id= {`header-description-${singleData.id}`}
                                        name={
                                            singleData.heading_type === "heading_descriptons"
                                                ? `lc_lpr_settings[page_recommendations][${singleData.id}][heading_descriptons]`
                                                : ""
                                        }
                                        className="heading-article wp-editor-area"
                                        value={singleData.heading_descriptons}
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
                                        singleData.heading_type === "heading" ? "block" : "none",
                                    }}
                                >
                                    <input
                                        type="text"
                                        name={
                                            singleData.heading_type === "heading"
                                                ? `lc_lpr_settings[page_recommendations][${singleData.id}][heading]`
                                                : ""
                                        }
                                        value={singleData.heading}
                                        onChange={(e) =>
                                            setInitialData({ ...initialData, heading: e.target.value })
                                        }
                                    />
                                </p>
                            </div>
                        </div>
                    </div> // end single recommendations
                ))}
            </div>
            <button
                type="button"
                onClick={newRecommendations}
            >Add New+</button>
        </div>
    );
}

export default PageRecommendations;