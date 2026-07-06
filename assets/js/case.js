const caseFilterButtons = document.querySelectorAll("[data-case-category]");
const caseCards = document.querySelectorAll("[data-case-card]");

caseFilterButtons.forEach((button) => {
  button.addEventListener("click", () => {
    const category = button.dataset.caseCategory;
    caseFilterButtons.forEach((item) => item.classList.toggle("is-active", item === button));
    caseCards.forEach((card) => {
      card.hidden = category !== "all" && card.dataset.category !== category;
    });
  });
});

const galleryButtons = [...document.querySelectorAll("[data-gallery-open]")];
const galleryDialog = document.querySelector("[data-gallery-dialog]");
const galleryImage = document.querySelector("[data-gallery-image]");
const galleryCaption = document.querySelector("[data-gallery-caption]");
let galleryIndex = 0;

const showGalleryImage = (index) => {
  if (!galleryButtons.length || !galleryImage || !galleryCaption) return;
  galleryIndex = (index + galleryButtons.length) % galleryButtons.length;
  const button = galleryButtons[galleryIndex];
  galleryImage.src = button.dataset.src;
  galleryImage.alt = button.dataset.alt || "施工事例写真";
  galleryCaption.textContent = button.dataset.caption || "";
};

galleryButtons.forEach((button, index) => {
  button.addEventListener("click", () => {
    showGalleryImage(index);
    galleryDialog?.showModal();
  });
});

document.querySelector("[data-gallery-close]")?.addEventListener("click", () => galleryDialog?.close());
document.querySelector("[data-gallery-prev]")?.addEventListener("click", () => showGalleryImage(galleryIndex - 1));
document.querySelector("[data-gallery-next]")?.addEventListener("click", () => showGalleryImage(galleryIndex + 1));
galleryDialog?.addEventListener("click", (event) => {
  if (event.target === galleryDialog) galleryDialog.close();
});
