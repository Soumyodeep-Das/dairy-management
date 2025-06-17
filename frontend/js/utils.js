// js/utils.js

export function qs(selector) {
  return document.querySelector(selector);
}

export function ce(tag, className = "", inner = "") {
  const el = document.createElement(tag);
  if (className) el.className = className;
  if (inner) el.innerHTML = inner;
  return el;
}

export function formatPrice(price) {
  return `₹${parseFloat(price).toFixed(2)}`;
}

export function formatDate(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleDateString("en-IN", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

export function getParam(key) {
  const url = new URL(window.location.href);
  return url.searchParams.get(key);
}
