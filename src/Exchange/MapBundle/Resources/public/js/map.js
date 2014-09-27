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
        var direction,
            value,
            elements,
            limits = {min: null, max: null},
            subscribedFunctions = [],
            exchanges,
            statistic,
            storage = {};

        function init() {
            if (typeof window.exchange !== 'array') {
                log.log('Exchanges storage is empdy or don\'t exists');
            }

            exchanges = window.exchange.exchanges;
            statistic = window.exchange.statistic;
        }

        function setDirection(newDirection) {
            direction = newDirection;

            return storage;
        }

        function setValue(newValue) {
            value = newValue;

            return storage;
        }

        function getElements() {
            return elements;
        }

        function getLimits(direction) {
            if (typeof direction != 'undefined') {
                return statistic[direction];
            }

            return limits;
        }

        function reloadElements() {
            var minLimit, maxLimit;

            elements = [];

            for (var i in exchanges) {
                if (
                    (exchanges[i].direction == direction)
//                    && (storage[i].value >= limits.min)
//                    && (storage[i].value <= limits.max)
                ) {
//                    if (!minLimit || minLimit > exchanges[i].value) {
//                        minLimit = exchanges[i].value;
//                    }
//
//                    if (!maxLimit || maxLimit < exchanges[i].value) {
//                        maxLimit = exchanges[i].value;
//                    }

                    elements.push(exchanges[i]);
                }
            }

            limits.min = statistic[direction].min;
            limits.max = statistic[direction].max;
            limits.optimal = statistic[direction].optimal;

            return storage;
        }

        function subscribeOnReload(fn) {
            subscribedFunctions.push(fn);
        }

        function updateEvent(fn) {
            log.log('updating positions...');
            reloadElements();

            for (var i in subscribedFunctions) {
                subscribedFunctions[i](storage);
            }

            if (typeof fn == 'function') {
                fn(storage);
            }
        }

        init();

        storage.setDirection = setDirection;
        storage.setValue = setValue;
        storage.getElements = getElements;
        storage.getLimits = getLimits;
        storage.onReload = subscribeOnReload;
        storage.update = updateEvent;

        return storage;
    })(logModule);

    var mapModule = (function(storage) {
        var myMap, cluster;

        function initMapOnReady() {
            myMap = createMap();

            cluster = new ymaps.Clusterer();
            myMap.geoObjects.add(cluster);

            storage.onReload(reloadCluster);

            reloadCluster(storage);
        }

        function createMap() {
            var map = new ymaps.Map('map', {
                center: [53.90, 27.53],
                zoom: 11
            });

            map.behaviors.enable('scrollZoom');// Включим масштабирование колесом мыши
            map.controls.add(
                new ymaps.control.ZoomControl()// Создание экземпляра элемента управления
            );

            return map;
        }

        function reloadCluster(storage)
        {
            var elements = storage.getElements(),
                objects = [];

            console.log('reload with');
            cluster.removeAll();

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

        ymaps.ready(initMapOnReady);
    })(storageModule);

    var filterModule = (function(storage) {
        var selector = {
                switch: '.js-filter-switch',
                radio: '.js-filter-radio',
                slider: '.js-filter-slider'
            };

        function changeDirection() {
            var switchVal = $(selector.switch).val(),
                radioVal = $(selector.radio + ' input:radio:checked').val(),
                direction = radioVal + '_' + switchVal,
                value = storage.getLimits(direction).optimal;

            console.log('change direction', direction, 'value', value);
            storage
                .setDirection(direction)
                .setValue(value)
                .update(updateLimits);

        }

        function changeValue() {
            var value = $(selector.slider).val();

            console.log('slider', value);
            storage.setValue(value).update();
        }

        function updateLimits(storage) {
            $(selector.slider).attr('min', storage.getLimits().min);
            $(selector.slider).attr('max', storage.getLimits().max);
            $(selector.slider).val(storage.getLimits().optimal);
            $(selector.slider).slider('refresh');

            console.log('updateLimits');
        }

        function init() {
            $(selector.switch).on('change', changeDirection);

            $(selector.radio).on('change', changeDirection);

            $(selector.slider).on('slidestop', changeValue);

//            storage.onReload(updateLimits);

            changeDirection();
        }

        $(document).on('pagecreate', '#page-map', init);
    })(storageModule);
})();