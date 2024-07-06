define([
    'ko',
    'uiElement', //'uiComponent',
    'mageUtils'
], function (ko, Element, utils) {
    'use strict';

    return Element.extend({
        defaults: {
            visible: true,
            template: 'Emagento_Comments/form/element/rating',
            ratingsData: [],
            //ratings: [], //ko.observableArray([]),
            uid: utils.uniqueid(),
            //links: {
            //    ratings: '${ $.provider }:${ $.dataScope }'
            //}
            //listens: {
            //    ratings: 'onUpdate'
            //}
        },

        initialize: function () {
            //debugger;
            this._super();
            console.log('initialize. value:', this.ratings);
            console.log('Ratings data on initialize:', this.ratingsData);
            //this.configureDataScope();
            //this.save= this.save.bind(this);
            this.setRatings(this.ratingsData);

            // Подписываемся на будущие изменения
            //this.value.subscribe(function (changes) {
            //    //console.log('value.subscribe. changes:', changes);
            //    this.subscribeToRatings();
            //}.bind(this), null, 'arrayChange');
            console.log('initialize. this:', this);

            //this.value = ko.observable(this.parseRatings(this.ratings));
            console.log('initialize. ratings:', this.ratings());

            //this.initObservable();
            /*
            if (this.source) {
                console.log('initialize. source before:', this.source);
                this.source. .on('save', this.onSubmit.bind(this), this.name);
                this.source.on('submit', this.onSubmit.bind(this), this.name);
                console.log('initialize. source after:', this.source);
            }
            */
            //console.log('Select rating method:', this.selectRating);
            //this.ratings = ko.observableArray(this.parseRatings(this.ratingsData));

            this.selectRating = this.selectRating.bind(this);

            return this;
        },
        /*
        initObservable: function () {
            console.log('initObservable. this.ratings:', this.ratings);
            this._super(); // observe(['value']);
            this.value = ko.observableArray(this.parseRatings(this.ratings));
            console.log('initObservable. value:', this.value);

            this.value.subscribe(function (changes) {
                console.log('changes:', changes)
                changes.forEach(function (change) {
                    console.log('change:', change);
                    if (change.status === 'added' && change.value.option_id) {
                        change.value.option_id.subscribe(function () {
                            this.onUpdate();
                        }.bind(this));
                    }
                }.bind(this));
            }, this, 'arrayChange');
            console.log('initObservable. this:', this);
            return this;
        },
        */

        /*
        // Подписка на изменения option_id для каждого элемента массива
        subscribeToRatings: function () {
            console.log('subscribeToRatings');
            this.ratings().forEach(function (rating) {
                if (rating.option_id && ko.isObservable(rating.option_id)) {
                    rating.option_id.subscribe(function (newValue) {
                        this.onUpdate();
                    }.bind(this));
                }
            }.bind(this));
        },
        */

        onUpdate: function (value) {
            console.log("Значение обновлено:", value);
        },

        /*
        setDifferedFromDefault: function () {
            //debugger;
            //this._super();
            var value = typeof this.value() != 'undefined' && this.value() !== null ? this.value() : '',
                defaultValue = typeof this.default != 'undefined' && this.default !== null ? this.default : '';

            console.log('setDifferedFromDefault. value defaultValue:', value, defaultValue);
        },
        */
        /*
        configureDataScope: function () {
            //console.log('configureDataScope. this.exports:', this.exports);
            //this.exports.value = this.provider + ':ratings_data'; // + this.exportDataLink;
            //console.log('configureDataScope. this.exports after:', this.exports);
            console.log('configureDataScope. this:', this);
        },
        */

        setRatings: function (dataRatings) {
            //console.log('setRatings. value before:', this.value());
            //debugger;
            //if (ko.isObservable(this.value)) {
            //    console.log('setRatings. value is observable:', this.value());
            //    return;
            // }
            console.log('setRatings. dataRatings:', dataRatings);
            //this.ratings = ko.observableArray(this.parseRatings(dataRatings)); //.extend({ trackArrayChanges: true });
            this.ratings = ko.observable(this.parseRatings(dataRatings));
            console.log('setRatings. ratings after:', this.ratings());
        },

        /*
        initObservable: function () {
            console.log('initObservable');
            this._super()
                // Инициализация ratings как observableArray
                .observe({ratings: []});

            // Преобразование каждого элемента ratings в observable
            this.updateRatings(this.ratingsData);

            return this;
        },
        */

        parseRatings: function (ratingsData) {
            //debugger;
            console.log('parseRatings. ratingsData:', ratingsData);
            if (!ratingsData) {
                return {};
            }

            var observableRatings = {};

            Object.keys(ratingsData).forEach(function (ratingId) {
                var rating = ratingsData[ratingId];
                // Преобразование option_id в observable
                rating.option_id = ko.observable(rating.option_id);
                // Преобразование option в observable
                rating.options = rating.options.map(function (option) {
                    return {
                        option_id: ko.observable(option.option_id),
                        value: ko.observable(option.value),
                        // Другие поля, которые нужно сделать observable, если необходимо
                        selected: ko.observable(option.value <= rating.value)
                    };
                });

                observableRatings[ratingId] = rating;
            });

            return observableRatings;
            /*
            // Предположим, ratingsData - это сырые данные, полученные от DataProvider
            var observableRatings = ratingsData.map(function (rating) {
                rating.option_id = ko.observable(rating.option_id);
                // Преобразование каждой опции в observable
                rating.options = rating.options.map(function (option) {
                    return {
                        option_id: ko.observable(option.option_id),
                        value: ko.observable(option.value),
                        // Другие поля, которые нужно сделать observable, если необходимо
                        selected: ko.observable(option.value <= rating.value)
                    };
                });

                return rating;
            });
            return observableRatings;
            */
        },
        /*
        parseRatings: function (ratingsData) {
            //console.log('Parsing ratingsData:', ratingsData);
            if (!ratingsData || !Array.isArray(ratingsData)) {
                console.error('Invalid ratingsData:', ratingsData);
                return [];
            }
            return ratingsData.map(function (rating) {
                //console.log('ratingsData.map(): ', rating);
                if (!Array.isArray(rating.options)) {
                    console.error('Invalid rating options:', rating);
                    rating.options = [];
                }
                rating.options = rating.options.map(function (option) {
                    console.log('rating.options.map():', option, rating.option_id);
                    //option.selected = ko.observable(option.option_id === rating.option_id);
                    option.selected = ko.observable(option.value <= rating.value);
                    return option;
                });
                rating.option_id = ko.observable(rating.option_id);
                return rating;
            });
        },
        */
        selectRating: function (option, ratingId) {
            console.log('selectRating. option ratingId:', option, ratingId);
            // Сбросить состояние selected для всех звезд
            var rating = this.ratings()[ratingId];
            rating.options.forEach(function(option) {
                option.selected(false);
            });

            // Установить selected для звезд с рейтингом меньшим или равным текущему рейтингу
            for (var i = 0; i < option.value(); i++) {
                rating.options[i].selected(true);
            }

            rating.option_id(option.option_id());

            var path= this.dataScope + '.' + ratingId + '.option_id';
            var value = ko.toJS(option.option_id());
            console.log('selectRating. path value:', path, value);
            this.source.set(path, value); //ko.toJS(option.option_id()));
            //var q = ko.toJS(this.ratings);
            //console.log('selectRating. js ratings:', q);
            //this.source.set('data.ratings', q);
            //this.ratings.valueHasMutated();
            console.log('selectRating. ratings:', this.ratings());
            console.log('selectRating. this:', this);
        }
        /*
        onSubmit: function () {
            console.log('onSubmit');
            var updatedRatingsData = this.ratings().map(function (rating) {
                return {
                    ...rating,
                    options: rating.options.map(function (option) {
                        return {
                            option_id: option.option_id(),
                            value: option.value()
                        };
                    })
                };
            });
            console.log('submit:', updatedRatingsData);

            // Обновление source с новыми данными
            this.source.set('data.ratings', updatedRatingsData);

            return this._super();
        }
        */
    });
});
