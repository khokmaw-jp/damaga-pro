const searchInput = document.querySelector("[data-faq-search]");
const cards = [...document.querySelectorAll("[data-faq-card]")];
const sections = [...document.querySelectorAll("[data-category-section]")];
const tabs = [...document.querySelectorAll("[data-category]")];
const emptyMessage = document.querySelector("[data-faq-empty]");
let activeCategory = "all";
let activeTerm = "";

const normalize = (value) => value.toLocaleLowerCase("ja").replace(/\s+/g, " ").trim();

const filterFaqs = () => {
  let visibleCount = 0;
  cards.forEach((card) => {
    const matchesCategory = activeCategory === "all" || card.dataset.category === activeCategory;
    const matchesTerm = !activeTerm || normalize(card.dataset.search || "").includes(activeTerm);
    const isVisible = matchesCategory && matchesTerm;
    card.hidden = !isVisible;
    if (isVisible) visibleCount += 1;
  });
  sections.forEach((section) => {
    section.hidden = ![...section.querySelectorAll("[data-faq-card]")].some((card) => !card.hidden);
  });
  if (emptyMessage) emptyMessage.hidden = visibleCount !== 0;
};

tabs.forEach((tab) => {
  tab.addEventListener("click", () => {
    activeCategory = tab.dataset.category || "all";
    tabs.forEach((item) => item.classList.toggle("is-active", item === tab));
    filterFaqs();
  });
});

searchInput?.addEventListener("input", () => {
  activeTerm = normalize(searchInput.value);
  filterFaqs();
});

document.querySelectorAll("[data-tag]").forEach((tag) => {
  tag.addEventListener("click", () => {
    activeCategory = "all";
    activeTerm = normalize(tag.dataset.tag || "");
    if (searchInput) searchInput.value = tag.dataset.tag || "";
    tabs.forEach((item) => item.classList.toggle("is-active", item.dataset.category === "all"));
    filterFaqs();
    searchInput?.focus();
  });
});

const initialTag = new URLSearchParams(window.location.search).get("tag");
if (initialTag && searchInput) {
  searchInput.value = initialTag;
  activeTerm = normalize(initialTag);
  filterFaqs();
}
