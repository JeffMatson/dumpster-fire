var selectPoint = function(params) {
    console.log(params);
};

var bodyPartSelector = {
    init: function(id, url, height, width) {
        var img = new Image();
        img.src = url;

        var canvas = jQuery('#body_canvas_' + id);
        var parentContainer = canvas.parent();

        var heightScale = height / width;

        var canvasWidth = parentContainer.width();

        var scaleWidth = canvasWidth / 100;
        var scaleHeight = height / 100;

        var canvasHeight = parentContainer.width() * heightScale;

        canvas.replaceWith('<canvas id="body_canvas_new_' + id + '" height="' + parentContainer.width() * heightScale + '" width="' + parentContainer.width() + '"></canvas>');

        canvas = jQuery('#body_canvas_new_' + id);

        canvas.drawImage({
            layer: true,
            source: img.src,
            fromCenter: false,
            width: canvasWidth,
            height: canvasHeight,
            x: 0,
            y: 0,
            click: function(layer) {
                jQuery(this).drawEllipse({
                    layer: true,
                    fillStyle: '#c33',
                    x: layer.eventX,
                    y: layer.eventY,
                    width: 25,
                    height: 25,
                }).drawLayers();

                var d = this.toDataURL("image/png");
                jQuery('.body_image_markup_' + id).val(d);
            },
        }).drawLayers();
    }
};
