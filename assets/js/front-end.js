const lcWpmethodsChatToggle = document.getElementById("lcWpmethodsChatToggle");
const lcWpmethodsChatContainer = document.getElementById("lcWpmethodsChatContainer");
const lcWpmethodsChatIcon = document.getElementById("lcWpmethodsChatIcon");

const lcWpmethodsUpdateToggleIcon = (isOpen) => {
  lcWpmethodsChatIcon.className = isOpen ? 'fas fa-times' : 'fas fa-comment-dots';
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
