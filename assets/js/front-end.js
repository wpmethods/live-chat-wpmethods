const lcWpmethodsChatToggle = document.getElementById("lcWpmethodsChatToggle");
const lcWpmethodsChatContainer = document.getElementById("lcWpmethodsChatContainer");
const lcWpmethodsChatIcon = document.getElementById("lcWpmethodsChatIcon");
const toggleIcon = lcWpmethodsChatIcon.getAttribute('data-toggle-icon');

const lcWpmethodsUpdateToggleIcon = (isOpen) => {
  lcWpmethodsChatIcon.className = isOpen ? 'fas fa-times' : toggleIcon;
};

lcWpmethodsChatToggle.addEventListener("click", () => {
  const isOpen = lcWpmethodsChatContainer.classList.toggle("lc-wpmethods-open");
  lcWpmethodsChatToggle.setAttribute("aria-expanded", isOpen);
  lcWpmethodsUpdateToggleIcon(isOpen);
});

lcWpmethodsChatToggle.addEventListener("keydown", (e) => {
  if (e.key === "Enter" || e.key === " ") {
    e.preventDefault();
    lcWpmethodsChatToggle.click();
  }
});

document.addEventListener("click", (e) => {
  if (!lcWpmethodsChatContainer.contains(e.target)) {
    lcWpmethodsChatContainer.classList.remove("lc-wpmethods-open");
    lcWpmethodsChatToggle.setAttribute("aria-expanded", "false");
    lcWpmethodsUpdateToggleIcon(false);
  }
});

document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    lcWpmethodsChatContainer.classList.remove("lc-wpmethods-open");
    lcWpmethodsChatToggle.setAttribute("aria-expanded", "false");
    lcWpmethodsUpdateToggleIcon(false);
  }
});



jQuery(document).ready(function($) {
  $('.lc-wpmethods-chat-btn').on('click', function(e) {
      e.preventDefault();

      const baseUrl = $(this).data('url');
      let baseMessage = $(this).data('base-message') || '';
      const isProduct = $(this).data('is-product') === 1 || $(this).data('is-product') === '1';
      const trackProduct = $(this).data('is-product-track') === 1;

      // Variation handling (only for product pages)
      if (isProduct && trackProduct) {
          let selectedVariation = '';
          let price = $('.woocommerce-variation-price .price').text().trim();
          let hasSelection = true;

          $('.variations select').each(function() {
              let value = $(this).val();
              if (!value) {
                  hasSelection = false;
              } else {
                  let label = $(this).closest('tr').find('label').text().trim();
                  selectedVariation += `${label}: ${value} `;
              }
          });

          let variationText = '';
          if (hasSelection && selectedVariation && price) {
              if (price.includes('–')) {
                  let prices = price.split('–').map(p => p.trim());
                  if (prices.length === 2) {
                      variationText = `Selected Variation: ${selectedVariation}- ${prices[0]}\n`;
                      variationText += `Original price was: ${prices[0]}\n`;
                      variationText += `*The discounted price is: ${prices[1]}.*`;
                  } else {
                      variationText = `*Selected Variation: ${selectedVariation}- ${price}*`;
                  }
              } else {
                  variationText = `*Selected Variation: ${selectedVariation}- ${price}*`;
              }

              // Remove any existing variations block
              baseMessage = baseMessage.replace(/Variations:\n-.*?\n(Description:|$)/s, '');
              baseMessage += `\n${variationText}`;
          }
      }

      // Encode message and prepare URL
      const encodedMessage = encodeURIComponent(baseMessage.trim());
      const finalUrl = baseUrl.includes('?') 
          ? `${baseUrl}&text=${encodedMessage}` 
          : `${baseUrl}?text=${encodedMessage}`;

      window.open(finalUrl, '_blank');
  });
});
