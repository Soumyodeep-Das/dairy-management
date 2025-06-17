// js/auth.js

export function setUserSession(user) {
  localStorage.setItem("user", JSON.stringify(user));
}

export function getUserSession() {
  const user = localStorage.getItem("user");
  return user ? JSON.parse(user) : null;
}

export function clearSession() {
  localStorage.removeItem("user");
}

export function requireAuth(role = null) {
  const user = getUserSession();
  if (!user || (role && user.role !== role)) {
    alert("Unauthorized access!");
    window.location.href = "../login.html";
  }
}
