(function() {
    var mapModule = (function() {
        ymaps.ready(initMapOnReady);

        var myMap;

        function initMapOnReady() {
            myMap = createMap();
            myMap.geoObjects.add(createObject());
        }

        function createMap() {
            var $mapElement = $('#map');

            var map = new ymaps.Map("map", {
                center: [53.90, 27.53],
                zoom: 11
            });

            // Включим масштабирование колесом мыши
            map.behaviors.enable('scrollZoom');
            // Создание экземпляра элемента управления
            map.controls.add(
                new ymaps.control.ZoomControl()
            );

            return map;
        }

        function createObject() {
            var myGeoObject = new ymaps.GeoObject({
                geometry: {
                    type: "Point",// тип геометрии - точка
                    coordinates: [53.90, 27.53] // координаты точки
                },
                properties: {
                    balloonContent: 'qwert yui',
                    balloonContentHeader: "Заголовок балуна",
                    balloonContentBody: 'Текст балуна № 1',
                    balloonContentFooter: 'footer',
                    iconContent:'12344p',
                    hintContent: "Подсказка",
                    clusterCaption: 'Геообъект № 1'
                }
            },{
                preset: "twirl#blueStretchyIcon"
            });

            return myGeoObject;
        }
    })();
})();