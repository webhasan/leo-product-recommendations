const fetchProducts = (url, nonce, exclude, page, category, query) => {
    return new Promise((resolve, reject) => {
        jQuery.ajax({
            url,
            method: "GET",
            data: {
              action: "lpr_fetch_products",
              nonce,
              post_id: exclude,
              page,
              category,
              query,
            }
        })
        .done(resolve)
        .fail(reject);
    });

}

export default fetchProducts;

