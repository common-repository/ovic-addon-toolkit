function changeControls(canvas, className, checked) {
    if (checked) {
        if (className == "bold") {
            canvas.getActiveObject().set("fontWeight", "bold");
        }
        if (className == "italic") {
            canvas.getActiveObject().set("fontStyle", "italic");
        }
        if (className == "underline") {
            canvas.getActiveObject().set("textDecoration", "underline");
        }
        if (className == "linethrough") {
            canvas.getActiveObject().set("textDecoration", "line-through");
        }
        if (className == "overline") {
            canvas.getActiveObject().set("textDecoration", "overline");
        }
    } else {
        if (className == "bold") {
            canvas.getActiveObject().set("fontWeight", "");
        }
        if (className == "italic") {
            canvas.getActiveObject().set("fontStyle", "");
        }
        if (className == "underline") {
            canvas.getActiveObject().set("textDecoration", "");
        }
        if (className == "linethrough") {
            canvas.getActiveObject().set("textDecoration", "");
        }
        if (className == "overline") {
            canvas.getActiveObject().set("textDecoration", "");
        }
    }
}

// Refresh page
function refresh() {
    setTimeout(function () {
        location.reload()
    }, 100);
}

document.querySelectorAll('.ovic-photo-editor').forEach(function (content) {
    var node        = content.querySelector('.editor'),
        controls    = content.querySelector('.bottom-controls'),
        TextControl = content.querySelector('.text-control'),
        Horizontal  = content.querySelectorAll('.btn-horizontal > *'),
        Vertical    = content.querySelectorAll('.btn-vertical > *'),
        Galleries   = content.querySelectorAll('.photo-galleries > *'),
        Download    = controls.querySelector('.download'),
        Delete      = controls.querySelector('.delete'),
        AddText     = controls.querySelector('.add-text'),
        Image       = controls.querySelector('.image'),
        Background  = controls.querySelector('.background'),
        bounding    = node.getBoundingClientRect(),
        canvas      = new fabric.Canvas(node, {
            width : parseInt(node.getAttribute('width')),
            height: parseInt(node.getAttribute('height')),
        }),
        setActive   = el => {
            [...el.parentElement.children].forEach(sib => sib.classList.remove('active'))
            el.classList.add('active')
        };

    // Add Image
    Image.addEventListener('change', function (e) {
        var file      = e.target.files[0];
        var reader    = new FileReader();
        reader.onload = function (f) {
            var data = f.target.result;
            fabric.Image.fromURL(data, function (img) {
                var oImg = img.set({
                    originX: 'center',
                    originY: 'center',
                    left   : bounding.width / 2,
                    top    : bounding.height / 2,
                    angle  : 0,
                }).scale(1);
                canvas.setActiveObject(oImg);
                canvas.add(oImg).renderAll();
                var dataURL = canvas.toDataURL({
                    format : 'png',
                    quality: 1
                });
            });
        };
        reader.readAsDataURL(file);
    });

    // Add Background
    Background.addEventListener('change', function (e) {
        var file      = e.target.files[0];
        var reader    = new FileReader();
        reader.onload = function (f) {
            var data = f.target.result;
            fabric.Image.fromURL(data, function (img) {
                // add background image
                canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                    scaleX: canvas.width / img.width,
                    scaleY: canvas.height / img.height
                });
            });
        };
        reader.readAsDataURL(file);
    });

    // Add Galleries
    Galleries.forEach((elem) => {
        elem.addEventListener('click', function (e) {
            e.preventDefault();
            var image = this.getAttribute('src');
            fabric.Image.fromURL(image, function (img) {
                // add background image
                canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                    scaleX: canvas.width / img.width,
                    scaleY: canvas.height / img.height
                });
            });
        });
    });

    // Add Text
    AddText.addEventListener('click', function (e) {
        var Text = new fabric.IText('Tap and Type', {
            originX    : 'center',
            originY    : 'center',
            left       : bounding.width / 2,
            top        : bounding.height / 2,
            fontFamily : 'Arial',
            fill       : '#000',
            stroke     : '#fff',
            strokeWidth: .1,
            fontSize   : 45,
            padding    : 5
        });
        canvas.setActiveObject(Text).add(Text);
        TextControl.hidden = false;
    });

    // Download
    Download.addEventListener('click', function (e) {
        this.href     = canvas.toDataURL({
            format : 'png',
            quality: 1
        });
        this.download = Download.getAttribute('data-filename')
    });

    // Delete selected object
    Delete.addEventListener('click', function (e) {
        e.preventDefault();
        var activeObject  = canvas.getActiveObject(),
            activeObjects = canvas.getActiveObjects();
        if (activeObject) {
            if (confirm('Are you sure?')) {
                if (activeObjects) {
                    activeObjects.forEach(function (object) {
                        canvas.remove(object);
                    });
                    canvas.discardActiveObject();
                    canvas.renderAll();
                } else {
                    canvas.remove(activeObject);
                }
            }
        }
    });
    // Change Position object
    Horizontal.forEach((elem) => {
        elem.addEventListener('click', function (e) {
            if (canvas.getActiveObject()) {
                var position = this.getAttribute('data-value'),
                    args     = {
                        left   : 10,
                        originX: 'left',
                    };
                if (position == 'right') {
                    args = {
                        left   : bounding.width - 10,
                        originX: 'right',
                    }
                }
                if (position == 'center') {
                    args = {
                        left   : bounding.width / 2,
                        originX: 'center',
                    }
                }
                canvas.getActiveObject().set(args);
                canvas.renderAll();
                setActive(this);

            }
            e.preventDefault();
        });
    });
    Vertical.forEach((elem) => {
        elem.addEventListener('click', function (e) {
            if (canvas.getActiveObject()) {
                var position = this.getAttribute('data-value'),
                    args     = {
                        top    : 10,
                        originY: 'top',
                    };
                if (position == 'bottom') {
                    args = {
                        top    : bounding.height - 10,
                        originY: 'bottom',
                    }
                }
                if (position == 'middle') {
                    args = {
                        top    : bounding.height / 2,
                        originY: 'center',
                    }
                }
                canvas.getActiveObject().set(args);
                canvas.renderAll();
                setActive(this);
            }
            e.preventDefault();
        });
    });

    // Edit Text
    var TextColor  = TextControl.querySelector('.text-color'),
        TextBG     = TextControl.querySelector('.background-color'),
        FontFamily = TextControl.querySelector('.font-family');

    TextColor.onchange  = function () {
        canvas.getActiveObject().setFill(this.value);
        canvas.renderAll();
    };
    TextBG.onchange     = function () {
        canvas.getActiveObject().setBackgroundColor(this.value);
        canvas.renderAll();
    };
    FontFamily.onchange = function () {
        canvas.getActiveObject().set('fontFamily', this.value);
        canvas.renderAll();
    };
    TextControl.querySelectorAll('[name="font_type"]').forEach(function (node) {
        var className = node.getAttribute('class');
        node.onclick  = function (e) {
            if (TextControl.querySelector('.' + className).checked) {
                changeControls(canvas, className, true);
            } else {
                changeControls(canvas, className, false);
            }
            canvas.renderAll();
        }
    });

    // canvas Event
    canvas.on({
        'selection:created': function (e) {
            if (e.target.type === 'i-text') {
                TextControl.hidden = false;
            }
        },
        'selection:updated': function (e) {
            if (e.target.type === 'i-text') {
                TextControl.hidden = false;
            }
        },
        'selection:cleared': function (e) {
            TextControl.hidden = true;
        }
    });

    fabric.util.addListener(fabric.document, 'keydown', function (e) {
        var activeObject = canvas.getActiveObject(),
            step         = 5;
        switch (e.keyCode) {
            case 38:  /* Up arrow */
                if (activeObject) {
                    e.preventDefault();
                    activeObject.top -= step;
                    canvas.renderAll();
                }
                break;
            case 40:  /* Down arrow  */
                if (activeObject) {
                    e.preventDefault();
                    activeObject.top += step;
                    canvas.renderAll();
                }
                break;
            case 37:  /* Left arrow  */
                if (activeObject) {
                    e.preventDefault();
                    activeObject.left -= step;
                    canvas.renderAll();
                }
                break;
            case 39:  /* Right arrow  */
                if (activeObject) {
                    e.preventDefault();
                    activeObject.left += step;
                    canvas.renderAll();
                }
                break;
            case 46:  /* delete */
                Delete.dispatchEvent(new Event('click'));
                break;

        }
    });

    // Do some initializing stuff
    fabric.Object.prototype.set({
        transparentCorners: false,
        cornerStyle       : 'circle',
        cornerColor       : '#22A7F0',
        borderColor       : '#22A7F0',
        cornerSize        : 10,
        padding           : 5
    });
});