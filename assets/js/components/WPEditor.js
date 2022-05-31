const { useEffect } = wp.element;

const initWPEditor = (id) => {
	setTimeout(() => {
		let { tinymce: tinymceObj, quicktags: quicktagsObj } =
			wp.editor.getDefaultSettings();
		wp.editor.initialize(id, {
			tinymce: {
				...tinymceObj,
				toolbar1:
					"formatselect,,forecolor,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_add_media,wp_adv",
				toolbar2:
					"strikethrough,hr,pastetext,removeformat,charmap,outdent,indent,undo,redo",
				plugins:
					"charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview",

				setup: function (editor) {
					editor.on("NodeChange", function (e) {
						editor.save();
					});
				},
			},
			quicktags: quicktagsObj,
		});
	}, 400);
};

const WPEditor = ({ onChange, id, ...options }) => {
	useEffect(function () {
		document.addEventListener("DOMContentLoaded", () => initWPEditor(id));
		// window.addEventListener("load", () => initWPEditor(id));
	}, []);

	return (
		<textarea
			style={{ height: 200 }}
			{...options}
			id={id}
			onChange={(e) => onChange(e.target.value)}
		></textarea>
	);
};

export default WPEditor;
