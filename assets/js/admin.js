jQuery(document).ready(function($) {
    $('.color-field').wpColorPicker();

    $('.lc-tab-button').click(function() {
        var tab_id = $(this).data('tab');

        $('.lc-tab-button').removeClass('active');
        $('.lc-tab-content').removeClass('active');

        $(this).addClass('active');
        $('#tab-' + tab_id).addClass('active');
    });

    $('.color-field').on('change', function() {
        let color = $(this).val();
        $('#lc-live-preview').css('background', color);
    });
});


// Initialize the repeater fields for adding social chat links
document.addEventListener("DOMContentLoaded", function () {
    const addButton = document.getElementById("cl-wpmethods-add-field");
    const container = document.getElementById("cl-wpmethods-repeater-fields");
    

    let counter = wpmesoche_data.counter || 0;
    let limit_links = wpmesoche_data.limit || 2;

    if (limit_links && counter < limit_links) {
        addButton.style.display = 'inline-block';
    }

    addButton?.addEventListener("click", function () {
        const totalFields = container.querySelectorAll(".cl-wpmethods-field-group").length;

        if (limit_links && totalFields >= limit_links) {
            addButton.style.display = 'none';
            return;
        }

        const fieldGroup = document.createElement("div");
        fieldGroup.className = "cl-wpmethods-field-group";
        fieldGroup.style.marginBottom = "15px";

        fieldGroup.innerHTML = `
            <div class="cl-wpmethods-preview-icon" style="margin-bottom: 8px;">
                <i class="fab fa-whatsapp"></i>
            </div>

            <div class="flex-cl-wpmethods">
                <label class="cl-label-wpm" for="cl-color-${counter}">Icon Color</label>
                <input type="text" id="cl-color-${counter}" name="lc_wpmethods_settings[lc_wpmethods_links][${counter}][color]" class="color-picker" placeholder="Pick Icon Color" />
            </div>

            <div class="flex-cl-wpmethods">
                <label class="cl-label-wpm" for="cl-url-${counter}">Enter URL</label>
                <input type="text" id="cl-url-${counter}" name="lc_wpmethods_settings[lc_wpmethods_links][${counter}][url]" placeholder="URL (Ex: https://wa.me/88017900000)" />
            </div>

            <div class="flex-cl-wpmethods">
                <label class="cl-label-wpm" for="cl-class-${counter}">Enter Icon Class</label>
                <input type="text" id="cl-class-${counter}" name="lc_wpmethods_settings[lc_wpmethods_links][${counter}][icon]" class="icon-input" placeholder="Enter Icon Class (e.g. fab fa-facebook)" />
            </div>

            <div class="flex-cl-wpmethods">
                <label class="cl-label-wpm" for="cl-label-${counter}">Enter Label</label>
                <input type="text" id="cl-label-${counter}" name="lc_wpmethods_settings[lc_wpmethods_links][${counter}][label]" placeholder="Enter Label (Ex: Whatsapp)" />
            </div>

            <div class="flex-cl-wpmethods">
                <label class="cl-label-wpm" for="cl-bgcolor-${counter}">Background Color</label>
                <input type="text" id="cl-bgcolor-${counter}" name="lc_wpmethods_settings[lc_wpmethods_links][${counter}][bg_color]" class="color-picker" placeholder="Pick Background Color" />
            </div>

            <div class="flex-cl-wpmethods">
                <label class="cl-label-wpm" for="cl-sbgcolor-${counter}">Gradient Color</label>
                <input type="text" id="cl-sbgcolor-${counter}" name="lc_wpmethods_settings[lc_wpmethods_links][${counter}][s_bg_color]" class="color-picker" placeholder="Gradient Color" />
            </div>

            <button type="button" class="cl-wpmethods-remove-field button" style="margin-top: 10px;">Remove</button>
        `;

        container.appendChild(fieldGroup);
        initializeColorPickers();
        counter++;

        if (limit_links && container.querySelectorAll(".cl-wpmethods-field-group").length >= limit_links) {
            addButton.style.display = 'none';
        }
    });

    container.addEventListener("click", function (e) {
        if (e.target.classList.contains("cl-wpmethods-remove-field")) {
            e.target.closest(".cl-wpmethods-field-group").remove();
            if (limit_links && container.querySelectorAll(".cl-wpmethods-field-group").length < limit_links) {
                addButton.style.display = '';
            }
        }
    });

    document.addEventListener("input", function (e) {
        if (e.target.classList.contains("icon-input")) {
            const iconClass = e.target.value.trim() || 'fab fa-whatsapp';
            const preview = e.target.closest('.cl-wpmethods-field-group').querySelector('.cl-wpmethods-preview-icon i');
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
