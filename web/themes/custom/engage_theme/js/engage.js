console.log("Starting");


// const submitButton = document.getElementById('edit-submit');
// submitButton.addEventListener('click', function () {
//     const loader = document.querySelector('.loading');
//     if (loader) {
//     loader.classList.remove('hidden');
//     }
// });


document.addEventListener("DOMContentLoaded", function () {
  // Select all engage buttons
  const engageButtons = document.querySelectorAll("[engage-button]");

  engageButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      const modalId = this.getAttribute("data-modal-toggle");
      const modal = document.getElementById(modalId);

      if (modal) {
        // Show the modal (remove hidden, add flex for Tailwind layout)
        modal.classList.remove("hidden");
        modal.classList.add("flex");
      } else {
        console.warn("Modal with ID", modalId, "not found.");
      }
    });
  });

  // Handle close buttons with [data-modal-hide]
  const hideButtons = document.querySelectorAll("[data-modal-hide]");
  hideButtons.forEach(function (closeBtn) {
    closeBtn.addEventListener("click", function () {
      const targetId = this.getAttribute("data-modal-hide");
      const targetModal = document.getElementById(targetId);

      if (targetModal) {
        targetModal.classList.add("hidden");
        targetModal.classList.remove("flex");
      }
    });
  });
});

document.querySelectorAll('.js-form-item input, .js-form-item select').forEach((input) => {
  const label = input.closest('.js-form-item')?.querySelector('label');

  if (label && !label.classList.contains('no-float-label')) {
    input.addEventListener('focus', () => {
      label.classList.add('floating');
    });
    input.addEventListener('blur', () => {
      if (!input.value) {
        label.classList.remove('floating');
      }
    });
    // Trigger once on load
    if (input.value) {

      label.classList.add('floating');
    }
  }
});


document.addEventListener("DOMContentLoaded", function () {
  const faqItems = document.querySelectorAll(".collapse");

  faqItems.forEach(item => {
    const title = item.querySelector(".collapse-title");
    title.addEventListener("click", () => {
      // Close all items
      faqItems.forEach(i => i.classList.remove("active"));
      // Open the clicked one
      item.classList.add("active");
    });
  });
});

function copied() {
  const element = document.querySelector('.copied_link');
  if (!element) return;

  element.innerHTML = "Link Copied!";
  element.classList.add("copied");

  setTimeout(() => {
    element.innerHTML = "Copy Link";
    element.classList.remove("copied");
  }, 3000);
}
