const fetchAppData = (url, nonce, post_id) => {
    return new Promise((resolve, reject) => {
        jQuery.ajax({
            url,
            method: "GET",
            data: {
              action: "lpr_initial_data",
              nonce,
              post_id
            }
        })
        .done(resolve)
        .fail(reject);
    });

}

export default fetchAppData;

