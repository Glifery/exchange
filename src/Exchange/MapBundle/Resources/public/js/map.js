(function() {
    var logModule = (function() {
        function log() {
            console.log.apply(console, arguments);
        }

        return {
            log: log
        }
    })();

    var storageModule = (function(log) {
        var direction, value, storage;

        function init() {
            if (typeof window.exchanges !== 'array') {
                log.log('Exchanges storage is empdy or don\'t exists');
            }

            storage = window.exchanges;
        }

        function setDirection(newDirection) {
            direction = newDirection;
        }

        function setValue(newValue) {
            value = newValue;
        }

        function getList() {
            var filteredValues = [];

            for (var i in storage) {
//                if ((storage[i].direction == direction) && (storage[i].value == value)) {
                    //TODO: greater than etc...
                    filteredValues.push(storage[i]);
//                }
            }

            return filteredValues;
        }

        init();

        return {
            setDirection: setDirection,
            setValue: setValue,
            getList: getList
        }
    })(logModule);

    var mapModule = (function(storage) {
//        ymaps.ready(initMapOnReady);

        var myMap, cluster;

        function initMapOnReady() {
            myMap = createMap();

            cluster = new ymaps.Clusterer();
            myMap.geoObjects.add(cluster);

            reloadCluster();
        }

        function createMap() {
            var map = new ymaps.Map("map", {
                center: [53.90, 27.53],
                zoom: 11
            });

            map.behaviors.enable('scrollZoom');// Включим масштабирование колесом мыши
            map.controls.add(
                new ymaps.control.ZoomControl()// Создание экземпляра элемента управления
            );

            return map;
        }

        function reloadCluster()
        {
            var elements,
                objects = [];

            cluster.removeAll();
            elements = storage.getList();

            for (var i in elements) {
                var object = createObject(elements[i]);
                objects.push(object);
            }

            cluster.add(objects);
        }

        function createObject(element) {
            var object = new ymaps.GeoObject({
                geometry: {
                    type: "Point",// тип геометрии - точка
                    coordinates: [element.office.latitude, element.office.longitude] // координаты точки
                },
                properties: {
                    balloonContent: 'qwert yui',
                    balloonContentHeader: element.office.bank.title,
                    balloonContentBody: element.office.address,
                    balloonContentFooter: element.office.title,
                    iconContent: element.value,
                    hintContent: element.office.bank.title,
                    clusterCaption: element.office.bank.title
                }
            },{
                preset: "twirl#blueStretchyIcon"
            });

            return object;
        }
    })(storageModule);

    var filterModule = (function(storage) {
        var selector = {
                switch: '.js-filter-switch',
                radio: '.js-filter-radio',
                slider: '.js-filter-slider'
            };

        function init() {
            console.log('init!', selector.slider);

            $(selector.switch).on('change', function() {
                console.log('switch', $(selector.switch).val());
            });

            $(selector.radio).on('change', function() {
                console.log('radio', $(selector.radio + ' input:radio:checked').val());
            });

            $(selector.slider).on('slidestop', function() {
                console.log('slider', $(selector.slider).val());
            });
        }

        $(document).on('pagecreate', '#page-map', init);
    })(storageModule);
})();