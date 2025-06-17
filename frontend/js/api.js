// js/api.js

const API_BASE = "backend"; // change to actual backend path if needed

export async function fetchJSON(url) {
  const res = await fetch(`../${url}`);
  if (!res.ok) throw new Error("Fetch error");
  return await res.json();
}

export async function postData(url, data) {
  const formData = new URLSearchParams(data).toString();
  const res = await fetch(`../${url}`, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: formData
  });
  return await res.text();
}

