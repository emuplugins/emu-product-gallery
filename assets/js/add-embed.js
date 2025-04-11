// Função global
function onCustomButtonClick() {

    const OriginalFrame = wp.media.view.MediaFrame.Select;

    const custom_data = wp.media.frame.state().props.get('custom_data');
    if (!custom_data) {
        alert('Informe uma URL válida.');
        return;
    }

    fetch(custom_embed_data.rest_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': custom_embed_data.nonce
        },
        body: JSON.stringify({
            oembed_url: custom_data
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector("#menu-item-library").click();
            document.querySelector("#menu-item-browse").click();
            wp.media.frame.content.get().collection.props.set({ignore: (+ new Date())});
            wp.media.frame.content.get().options.selection.reset();
        } else {
            alert(data.message || 'Erro ao adicionar o embed.');
        }
    })
}

jQuery(document).ready(function ($) {

    // Define o controller (estado personalizado)
    wp.media.controller.CustomGlobalState = wp.media.controller.State.extend({
        initialize: function () {
            this.props = new Backbone.Model({ custom_data: '' });
            this.props.on('change:custom_data', this.refresh, this);
        },

        refresh: function () {
            this.frame.toolbar.get().refresh();
        },

        customAction: function () {
            onCustomButtonClick();
        }
    });

    // Define a toolbar
    wp.media.view.Toolbar.CustomGlobalToolbar = wp.media.view.Toolbar.extend({
        initialize: function () {
            _.defaults(this.options, {
                event: 'custom_global_event',
                close: false,
                items: {
                    custom_global_event: {
                        text: 'Adicionar',
                        style: 'primary',
                        priority: 80,
                        requires: false,
                        click: this.customAction
                    }
                }
            });

            wp.media.view.Toolbar.prototype.initialize.apply(this, arguments);
        },

        refresh: function () {
            const custom_data = this.controller.state().props.get('custom_data');
            this.get('custom_global_event').model.set('disabled', !custom_data);
            wp.media.view.Toolbar.prototype.refresh.apply(this, arguments);
        },

        customAction: function () {
            this.controller.state().customAction();
        }
    });

    wp.media.view.CustomGlobalContent = wp.media.View.extend({
        className: 'add-embed-wrapper',

        events: {
            'input input': 'updateData',
            'change input': 'updateData',
            'click #botao-personalizado': 'onCustomButtonClick'
        },

        initialize: function () {
            this.input = $('<input>', {
                type: 'text',
                placeholder: 'Digite a URL',
                style: 'width: 100%; margin-top: 10px;'
            });

            this.button = $('<button>', {
                id: 'botao-personalizado',
                class: 'button button-secondary',
                text: 'Enviar URL',
                style: 'margin-top: 10px; display: block;'
            });

            this.$el.append('<h3>Adicionar vídeo do youtube</h3>');
            this.$el.append(this.input);
            this.$el.append(this.button);

            this.model.on('change:custom_data', this.render, this);
        },

        render: function () {
            this.input.val(this.model.get('custom_data'));
            return this;
        },

        updateData: function (event) {
            this.model.set('custom_data', event.target.value);
        },

        onCustomButtonClick: function () {
            onCustomButtonClick(); // Usa a função global
        }
    });

    // Sobrescreve todos os MediaFrames para adicionar a aba personalizada globalmente
    const OriginalFrame = wp.media.view.MediaFrame.Select;

    wp.media.view.MediaFrame.Select = OriginalFrame.extend({
        initialize: function () {
            OriginalFrame.prototype.initialize.apply(this, arguments);

            wp.media.frame = this; // Torna acessível globalmente

            this.states.add([
                new wp.media.controller.CustomGlobalState({
                    id: 'embed-global',
                    menu: 'default',
                    content: 'custom-global-content',
                    title: 'Youtube Video',
                    priority: 200,
                    toolbar: 'main-embed-global'
                })
            ]);

            this.on('content:render:custom-global-content', this.renderCustomContent, this);
            this.on('toolbar:create:main-embed-global', this.createCustomToolbar, this);
            this.on('toolbar:render:main-embed-global', this.renderCustomToolbar, this);
        },

        createCustomToolbar: function (toolbar) {
            toolbar.view = new wp.media.view.Toolbar.CustomGlobalToolbar({
                controller: this
            });
        },

        renderCustomToolbar: function () {
            // Ponto para futuras customizações se necessário
        },

        renderCustomContent: function () {
            const view = new wp.media.view.CustomGlobalContent({
                controller: this,
                model: this.state().props
            });
            this.content.set(view);
        }
    });

});
