jQuery(document).ready(function($) {
    $('.color-field').wpColorPicker();

    $('.wpmesoch-admin-tab-button').click(function() {
        var tab_id = $(this).data('tab');

        $('.wpmesoch-admin-tab-button').removeClass('active');
        $('.wpmesoch-admin-tab-content').removeClass('active');

        $(this).addClass('active');
        $('#tab-' + tab_id).addClass('active');
    });

    $('.color-field').on('change', function() {
        let color = $(this).val();
        $('#wpmesoch-admin-live-preview').css('background', color);
    });
});


// Initialize the repeater fields for adding social chat links
document.addEventListener("DOMContentLoaded", function () {
    const addButton = document.getElementById("wpmesoch-admin-add-field");
    const container = document.getElementById("wpmesoch-admin-repeater-fields");
    

    let counter = wpmesoch_data.counter || 0;
    let limit_links = wpmesoch_data.limit || 2;

    if (limit_links && counter < limit_links) {
        addButton.style.display = 'inline-block';
    }

    addButton?.addEventListener("click", function () {
        const totalFields = container.querySelectorAll(".wpmesoch-admin-field-group").length;

        if (limit_links && totalFields >= limit_links) {
            addButton.style.display = 'none';
            return;
        }

        const fieldGroup = document.createElement("div");
        fieldGroup.className = "wpmesoch-admin-field-group";
        fieldGroup.style.marginBottom = "15px";

        fieldGroup.innerHTML = `
            <div class="wpmesoch-admin-preview-icon" style="margin-bottom: 8px;">
                <i class="fab fa-whatsapp"></i>
            </div>

            <div class="flex-wpmesoch-admin">
                <label class="cl-label-wpm" for="cl-color-${counter}">Icon Color</label>
                <input type="text" id="cl-color-${counter}" name="wpmesoch_settings[wpmesoch_links][${counter}][color]" class="color-picker" placeholder="Pick Icon Color" />
            </div>

            <div class="flex-wpmesoch-admin">
                <label class="cl-label-wpm" for="cl-url-${counter}">Enter URL</label>
                <input type="text" id="cl-url-${counter}" name="wpmesoch_settings[wpmesoch_links][${counter}][url]" placeholder="URL (Ex: https://wa.me/88017900000)" />
            </div>

            <div class="flex-wpmesoch-admin">
                <label class="cl-label-wpm" for="cl-class-${counter}">Enter Icon Class</label>
                <input type="text" id="cl-class-${counter}" name="wpmesoch_settings[wpmesoch_links][${counter}][icon]" class="icon-input" placeholder="Enter Icon Class (e.g. fab fa-facebook)" />
            </div>

            <div class="flex-wpmesoch-admin">
                <label class="cl-label-wpm" for="cl-label-${counter}">Enter Label</label>
                <input type="text" id="cl-label-${counter}" name="wpmesoch_settings[wpmesoch_links][${counter}][label]" placeholder="Enter Label (Ex: Whatsapp)" />
            </div>

            <div class="flex-wpmesoch-admin">
                <label class="cl-label-wpm" for="cl-bgcolor-${counter}">Background Color</label>
                <input type="text" id="cl-bgcolor-${counter}" name="wpmesoch_settings[wpmesoch_links][${counter}][bg_color]" class="color-picker" placeholder="Pick Background Color" />
            </div>

            <div class="flex-wpmesoch-admin">
                <label class="cl-label-wpm" for="cl-sbgcolor-${counter}">Gradient Color</label>
                <input type="text" id="cl-sbgcolor-${counter}" name="wpmesoch_settings[wpmesoch_links][${counter}][s_bg_color]" class="color-picker" placeholder="Gradient Color" />
            </div>

            <button type="button" class="wpmesoch-admin-remove-field button" style="margin-top: 10px;">Remove</button>
        `;

        container.appendChild(fieldGroup);
        initializeColorPickers();
        counter++;

        if (limit_links && container.querySelectorAll(".wpmesoch-admin-field-group").length >= limit_links) {
            addButton.style.display = 'none';
        }
    });

    container.addEventListener("click", function (e) {
        if (e.target.classList.contains("wpmesoch-admin-remove-field")) {
            e.target.closest(".wpmesoch-admin-field-group").remove();
            if (limit_links && container.querySelectorAll(".wpmesoch-admin-field-group").length < limit_links) {
                addButton.style.display = '';
            }
        }
    });

    document.addEventListener("input", function (e) {
        if (e.target.classList.contains("icon-input")) {
            const iconClass = e.target.value.trim() || 'fab fa-whatsapp';
            const preview = e.target.closest('.wpmesoch-admin-field-group').querySelector('.wpmesoch-admin-preview-icon i');
            preview.className = iconClass;
        }
    });

    new Sortable(container, {
        animation: 150,
        ghostClass: 'sortable-ghost'
    });

    function initializeColorPickers() {
        jQuery('.color-picker').wpColorPicker();
    }

    jQuery(document).ready(function () {
        initializeColorPickers();
    });
});
