CKEDITOR.editorConfig = function( config ) {
	
	config.toolbarGroups = [
		{ name: 'clipboard', groups: [ 'undo', 'clipboard' ] },
		{ name: 'links', groups: [ 'links' ] },
		{ name: 'insert', groups: [ 'insert' ] },
		{ name: 'forms', groups: [ 'forms' ] },
		{ name: 'others', groups: [ 'others' ] },
		{ name: 'paragraph', groups: [ 'list' ] },
		{ name: 'styles', groups: [ '' ] },
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] }
	];

	config.removeButtons = 'Underline,Subscript,Superscript,Styles,NumberedList,Anchor,Unlink,PasteText,PasteFromWord,Scayt,HorizontalRule,SpecialChar,Maximize,Bold,RemoveFormat,Italic,Strike,Indent,Outdent,Blockquote,About';
	
	config.removeDialogTabs = 'image:advanced;image:Link;link:advanced;link:upload';
	
};
