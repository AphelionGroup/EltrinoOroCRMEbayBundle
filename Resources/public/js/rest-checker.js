/* global:define */
define(['jquery', 'underscore', 'routing', 'backbone', 'orotranslation/js/translator',
    'oronavigation/js/navigation', 'oroui/js/messenger'
    ], function ($, _, routing, Backbone, __, Navigation, messenger) {
    "use strict";

        return Backbone.View.extend({
            events: {
                'click': 'processClick'
            },

            /**
             * Check url
             * @property string
             */
            route:           'eltrino_ebay_rest_check',
            url:             null,
            id:              null,
            requiredOptions: [ ],

            resultTemplate: _.template(
                '<div class="alert alert-<%= type %> connection-status"><%= message %></div>'
            ),

            connectorTemplate: _.template(
                '<div class="oro-clearfix">' +
                '<input type="checkbox" id="oro_integration_channel_form_connectors_<%= i %>" ' +
                'name="oro_integration_channel_form[connectors][]" value="<%= name %>">' +
                '<label for="oro_integration_channel_form_connectors_<%= i %>"><%= label %></label>' +
                '</div>'
            ),

            initialize: function (options) {
                this.id = options.transportEntityId || null;
                this.url = this.getUrl();

                var requiredMissed = this.requiredOptions.filter(function (option) {
                    return _.isUndefined(options[option]);
                });
                if (requiredMissed.length) {
                    throw new TypeError('Missing required option(s): ' + requiredMissed.join(','));
                }
            },

            getUrl: function (type) {
                var params = {id: this.id};
                if (type !== undefined) {
                    params.type = type;
                    params.type = type;
                }

                return routing.generate(this.route, params);
            },

            /**
             * Click handler
             *
             * @param e
             */
            processClick: function (e) {
                var data = this.$el.parents('form').serializeArray();
                var typeData = _.filter(data, function (field) {
                    return field.name.indexOf('[type]') !== -1;
                });
                if (typeData.length) {
                    typeData = typeData[0].value;
                }

                data = _.filter(data, function (field) {
                    return field.name.indexOf('[transport]') !== -1;
                });
                data = _.map(data, function (field) {
                    field.name = field.name.replace(/.+\[(.+)\]$/, 'rest-check[$1]')
                    return field;
                });
                var navigation = Navigation.getInstance();
                if (navigation) {
                    navigation.loadingMask.show();
                }
                $.post(this.getUrl(typeData), data, _.bind(this.responseHandler, this), 'json')
                    .always(_.bind(function (respose, status) {
                        if (navigation) {
                            navigation.loadingMask.hide();
                        }
                        if (status !== 'success') {
                            this.renderResult('error', __('Error occurred during check request, please try later!'));
                        }
                    }, this));
            },

            /**
             * Handler ajax response
             *
             * @param res {}
             */
            responseHandler: function (res) {
                var success = res.success || false,
                    message = success ? __('Connection succeed.') : __('Parameters are not valid!');

                if (success) {
                    var form = this.$el.parents('form');
                }

                this.renderResult(success ? 'success' : 'error', message);
            },

            /**
             * Render check result message
             *
             * @param type string
             * @param message string
             */
            renderResult: function (type, message) {
                messenger.notificationFlashMessage(type, message, {container: this.$el.parent(), template: this.resultTemplate});
            }
        });
    });
