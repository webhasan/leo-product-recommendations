const fetchCategories = (url, nonce) => {
    return new Promise((resolve, reject) => {
        jQuery.ajax({
            url,
            method: "GET",
            data: {
              action: "lpr_fetch_categories",
              nonce
            }
        })
        .done(resolve)
        .fail(reject);
    });

}

export default fetchCategories;

