define(function() {
    var init = function() {
        var map = new AMap.Map('container', {
            resizeEnable: true,
            rotateEnable:true,
            pitchEnable:true,
            features:['bg','road', 'building', 'point'],
            pitch:0,
            rotation:0,
            viewMode:'3D',
            buildingAnimation:true,
            expandZoomRange:true,
        });
        map.addControl(new AMap.ControlBar({
            showZoomBar:false,
            showControlButton:true,
            position:{
                right:'10px',
                top:'10px'
            }
        }));
    }
    return {
        init: init
    };
});