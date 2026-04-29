// =====================================================
// Gympass SPA - app.js  Willis K
// =====================================================
/* =============================
   LOCAL STORAGE WRAPPER
============================= */
const LS = {
  user: () => localStorage.getItem("booking_user") || "",
  setUser: u => u ? localStorage.setItem("booking_user", u) : localStorage.removeItem("booking_user"),
  bookings: () => JSON.parse(localStorage.getItem("booking_bookings") || "[]"),
  saveBookings: arr => localStorage.setItem("booking_bookings", JSON.stringify(arr)),
  lastSearch: () => localStorage.getItem("booking_lastsearch") || "",
  setLastSearch: t => localStorage.setItem("booking_lastsearch", t),
  participants: () => JSON.parse(localStorage.getItem("participants") || "[]"),
  saveParticipants: arr => localStorage.setItem("participants", JSON.stringify(arr))
};
/* =============================
   FALLBACK / MOCK DATA
============================= */
const MOCK_RESOURCES = [
  { id: "BodyPump 45", name: "BodyPump 45", capacity: 18, instructor: "Emma", time: "18:00" },
  { id: "Spinning Intensiv 50", name: "Spinning Intensiv 50", capacity: 14, instructor: "Johan", time: "07:00" },
  { id: "Yoga Flow 60", name: "Yoga Flow 60", capacity: 16, instructor: "Sara", time: "12:30" },
  { id: "CrossFit Intro 45", name: "CrossFit Intro 45", capacity: 12, instructor: "Karl", time: "17:30" },
  { id: "Core & Strech 30", name: "Core & Stretch 30", capacity: 20, instructor: "Lina", time: "09:30" },
];
/* =============================
   API HELPERS
============================= */
const API_BASE = '../API/booking/';
async function apiFetch(path, body = null) {
  const url = API_BASE + path;
  try {
    const opts = body ? {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    } : { method: 'GET' };
    const res = await fetch(url, opts);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return await res.json();
  } catch (err) {
    console.warn('API-fel:', err);
    throw err;
  }
}
/* =============================
   GLOBAL STATE
============================= */
let RESOURCES = [];
/* =============================
   SPA NAVIGATION
============================= */
const screens = document.querySelectorAll('.screen');
const navLinks = document.querySelectorAll('#main-nav a');

function showScreen(id, push = true, extraState = {}) {
  screens.forEach(s => s.classList.toggle('active', s.id === id));

  if (push) {
    history.pushState({ screen: id, ...extraState }, "", `#${id}`);
  }

  document.dispatchEvent(
    new CustomEvent("screen:show", {
      detail: { screen: id, state: extraState }
    })
  );
}

navLinks.forEach(a => a.addEventListener('click', e => {
  e.preventDefault();

  const screen = a.dataset.screen;

  // Dessa sidor kräver att användaren är inloggad
  const protectedScreens = ["register", "search", "book", "history", "canvas-demo"];

  // Hämtar inloggad användare från localStorage
  const user = localStorage.getItem("booking_user");

  if (protectedScreens.includes(screen) && !user) {
    alert("Du måste logga in först.");
    showScreen("welcome");
    return;
  }

  showScreen(screen);
}));

window.addEventListener('popstate', e => {
  const id = (e.state?.screen) || location.hash.slice(1) || 'welcome';
  screens.forEach(s => s.classList.toggle('active', s.id === id));
});
/* =============================
   INLOGGNINGSSKYDD
============================= */
document.addEventListener("screen:show", e => {
  // Endast dessa skärmar kräver inloggning för att visas
  const protectedScreens = ['search', 'book', 'history'];

  if (protectedScreens.includes(e.detail.screen) && !LS.user()) {
    alert("Du måste vara inloggad för att använda denna funktion!");
    showScreen('welcome');
    return;
  }

  // Register-skärmen får visas för alla – vi kontrollerar inloggning vid submit istället

  if (e.detail.screen === 'history') renderHistory();
  if (e.detail.screen === 'canvas-demo') initCanvasDemo();
});
/* =============================
   LOGIN / LOGOUT
============================= */
function updateLoginUI() {
  const user = LS.user();
  document.getElementById("not-logged").style.display = user ? "none" : "block";
  document.getElementById("logged").style.display = user ? "block" : "none";
  if (user) document.getElementById("user-display").textContent = user;
  document.getElementById("last-search").textContent = LS.lastSearch() || "-";
}
document.getElementById("login-form").addEventListener('submit', e => {
  e.preventDefault();
  const name = document.getElementById("login-name").value.trim();
  if (!name) return alert("Ange ett namn!");
  LS.setUser(name);
  updateLoginUI();
  showScreen('welcome');
});
document.getElementById('logout-btn').addEventListener('click', () => {
  LS.setUser('');
  updateLoginUI();
  showScreen('welcome');
});
document.getElementById('go-mybookings').addEventListener('click', () => showScreen('history'));
/* =============================
   REGISTER PARTICIPANTS
============================= */
const registerForm = document.getElementById('register-form');
const registeredList = document.getElementById('registered-list');
const regFeedback = document.getElementById('reg-feedback');

function renderRegisteredParticipants() {
  registeredList.innerHTML = '';
  const participants = LS.participants();
  if (!participants.length) {
    registeredList.innerHTML =
      '<li class="muted">Inga registrerade deltagare ännu.</li>';
    return;
  }
  participants.forEach(p => {
    const li = document.createElement('li');
    li.textContent = `${p.fname} ${p.lname} (${p.email}) `;
    const btn = document.createElement('button');
    btn.textContent = 'Ta bort';
    btn.addEventListener('click', () => {
      const updated = participants.filter(x => x.email !== p.email);
      LS.saveParticipants(updated);
      renderRegisteredParticipants();
    });
    li.appendChild(btn);
    registeredList.appendChild(li);
  });
}

registerForm.addEventListener('submit', async e => {
  e.preventDefault();

  // Kontrollera inloggning först – ger tydlig feedback om man inte är inloggad
  if (!LS.user()) {
    showFeedback(regFeedback, 'Du måste vara inloggad för att registrera en deltagare!', false);
    return;
  }

  const emailInput = document.getElementById('email');
  emailInput.style.borderColor = '';
  const fname = document.getElementById('fname').value.trim();
  const lname = document.getElementById('lname').value.trim();
  const email = emailInput.value.trim().toLowerCase();

  // ===== Validering =====
  if (!fname || !lname || !email) {
    showFeedback(regFeedback, 'Fyll i alla obligatoriska fält!', false);
    return;
  }
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    showFeedback(regFeedback, 'Ange en giltig e-postadress!', false);
    emailInput.style.borderColor = '#e74c3c';
    setTimeout(() => (emailInput.style.borderColor = ''), 3000);
    return;
  }
  if (LS.participants().some(p => p.email === email)) {
    showFeedback(regFeedback, 'E-postadressen är redan registrerad!', false);
    emailInput.style.borderColor = '#e74c3c';
    setTimeout(() => (emailInput.style.borderColor = ''), 3000);
    return;
  }

  const participant = {
    fname,
    lname,
    email,
    age: document.getElementById('age').value || null,
    notes: document.getElementById('notes').value.trim() || null,
    newsletter: document.getElementById('newsletter').checked ? 1 : 0
  };

  // ===== API-försök + fallback =====
  try {
    await apiFetch('makecustomer_XML.php', participant);
    showFeedback(regFeedback, 'Deltagare registrerad på servern!', true);
  } catch {
    showFeedback(
      regFeedback,
      'Server ej tillgänglig – deltagare sparad lokalt.',
      true
    );
  }

  LS.saveParticipants([...LS.participants(), participant]);
  renderRegisteredParticipants();
  registerForm.reset();
});
/* =============================
   FEEDBACK HELPER
============================= */
function showFeedback(el, msg, ok = true) {
  el.textContent = msg;
  el.className = `feedback show ${ok ? 'ok' : 'err'}`;
  setTimeout(() => el.className = 'feedback hidden', 3000);
}
/* =============================
   SEARCH
============================= */
const searchTermInput = document.getElementById('search-term');
const searchResults = document.getElementById('search-results');
function updateSearchResults() {
  const term = searchTermInput.value.trim().toLowerCase();
  LS.setLastSearch(term);
  const source = RESOURCES.length ? RESOURCES : MOCK_RESOURCES;
  const hits = source.filter(r =>
    r.name.toLowerCase().includes(term) || r.instructor.toLowerCase().includes(term)
  );
  searchResults.innerHTML = '';
  if (!hits.length) {
    searchResults.innerHTML = '<p class="muted">Inga pass hittades.</p>';
    return;
  }
  const table = document.createElement('table');
  table.innerHTML = `<thead><tr><th>Pass</th><th>Instruktör</th><th>Tid</th><th>Platser</th></tr></thead><tbody></tbody>`;
  const tbody = table.querySelector('tbody');
  hits.forEach(r => {
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${r.name}</td><td>${r.instructor}</td><td>${r.time}</td><td>${r.capacity}</td>`;
    tr.style.cursor = 'pointer';
    tr.onclick = () => {
      document.getElementById('book-resource').value = r.id;
      showScreen('book');
      renderSeats(r.id);
    };
    tbody.appendChild(tr);
  });
  searchResults.appendChild(table);
}
searchTermInput.addEventListener('input', updateSearchResults);
document.getElementById('search-form').addEventListener('submit', e => {
  e.preventDefault();
  updateSearchResults();
});
/* =============================
   BOOKING
============================= */
const bookResource = document.getElementById('book-resource');
const bookDate = document.getElementById('book-date');
const resourceVisual = document.getElementById('resource-visual');
const bookFeedback = document.getElementById('book-feedback');
function populateBookResources() {
  const source = RESOURCES.length ? RESOURCES : MOCK_RESOURCES;
  bookResource.innerHTML = source.map(r => `<option value="${r.id}">${r.name} – ${r.time}</option>`).join('');
}
async function getTakenSeats(id, date) {
  try {
    const data = await apiFetch('getbookings_JSON.php', { resourceId: id, date });
    return Array.isArray(data) ? data.map(b => +b.seat) : [];
  } catch {
    return LS.bookings().filter(b => b.resourceId === id && b.date === date).map(b => +b.seat);
  }
}
async function renderSeats(resourceId) {
  const res = (RESOURCES.length ? RESOURCES : MOCK_RESOURCES).find(r => r.id === resourceId);
  if (!res) return;
  resourceVisual.innerHTML = '';
  const taken = bookDate.value ? await getTakenSeats(resourceId, bookDate.value) : [];
  for (let i = 1; i <= res.capacity; i++) {
    const div = document.createElement('div');
    div.className = 'seat';
    div.textContent = i;
    if (taken.includes(i)) div.classList.add('taken');
    div.onclick = () => {
      if (div.classList.contains('taken')) return;
      resourceVisual.querySelectorAll('.seat').forEach(s => s.classList.remove('selected'));
      div.classList.add('selected');
    };
    resourceVisual.appendChild(div);
  }
}
bookResource.addEventListener('change', () => renderSeats(bookResource.value));
bookDate.addEventListener('change', () => renderSeats(bookResource.value));
document.getElementById('book-form').addEventListener('submit', async e => {
  e.preventDefault();
  const date = bookDate.value;
  if (!date) return showFeedback(bookFeedback, 'Välj datum!', false);
  const selected = resourceVisual.querySelector('.seat.selected');
  if (!selected) return showFeedback(bookFeedback, 'Välj en plats!', false);
  const booking = {
    customername: LS.user(),
    user: LS.user(),
    resourceId: bookResource.value,
    date,
    seat: +selected.textContent,
    note: document.getElementById('book-note').value.trim()
  };
  try {
    await apiFetch('addbooking_JSON.php', booking);
    showFeedback(bookFeedback, 'Bokning klar!', true);
  } catch {
    LS.saveBookings([...LS.bookings(), booking]);
    showFeedback(bookFeedback, 'Bokning sparad lokalt (ingen kontakt med servern)', true);
  }
  setTimeout(() => {
    document.getElementById('book-form').reset();
    renderSeats(bookResource.value);
  }, 1000);
});
/* =============================
   HISTORY (React + JSX via index.html)
============================= */
async function loadCustomerBookingsFromServer(user) {
  try {
    const data = await apiFetch('getcustomerbookings_JSON.php', { customername: user });
    return Array.isArray(data) ? data : [];
  } catch {
    return LS.bookings().filter(b => b.user === user);
  }
}
async function deleteBookingOnServer(booking) {
  try {
    await apiFetch('deletebooking_JSON.php', booking);
  } catch {
    const all = LS.bookings();
    LS.saveBookings(all.filter(b =>
      !(b.user === booking.user &&
        b.resourceId === booking.resourceId &&
        b.date === booking.date &&
        b.seat === booking.seat)
    ));
  }
}
async function renderHistory() {
  const root = document.getElementById('history-root');
  const user = LS.user();
  let bookings = await loadCustomerBookingsFromServer(user);
  const deleteBooking = async (index) => {
    await deleteBookingOnServer(bookings[index]);
    bookings = await loadCustomerBookingsFromServer(user);
    ReactDOM.render(
      React.createElement(window.HistoryApp, { bookings, onDelete: deleteBooking }),
      root
    );
  };
  ReactDOM.render(
    React.createElement(window.HistoryApp, { bookings, onDelete: deleteBooking }),
    root
  );
}
/* =============================
   LOAD RESOURCES
============================= */
async function loadResources() {
  try {
    const resp = await fetch(`${API_BASE}getresources_XML.php`);
    const text = await resp.text();
    const xml = new DOMParser().parseFromString(text, 'application/xml');
    RESOURCES = Array.from(xml.querySelectorAll('resource')).map(r => ({
      id: r.querySelector('id').textContent,
      name: r.querySelector('namn').textContent,
      capacity: parseInt(r.querySelector('platser').textContent),
      instructor: r.querySelector('instruktor')?.textContent || '',
      time: r.querySelector('tid')?.textContent || ''
    }));
  } catch (err) {
    console.warn('Kunde inte hämta resurser från servern, använder mock-data');
    RESOURCES = MOCK_RESOURCES;
  }
  populateBookResources();
  renderSeats(bookResource.value || MOCK_RESOURCES[0].id);
}
/* =============================
   CANVAS DEMO - roterande vektorgrafik med klippning (Amoebaface från konverterare)
============================= */
function initCanvasDemo() {
  const canvas = document.getElementById("main-canvas");
  if (!canvas) return;
  const ctx = canvas.getContext("2d");

  let angle = 0;
  let clipRadius = 0;          // Startvärde för pulserande radie
  const maxClipRadius = 250;   // Max storlek på cirkeln (anpassa efter canvas-storlek)
  let clipGrowing = true;      // Växande/minskande riktning

  function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.save();

    // ────────────────────────────────────────────────
    // ANIMERAD KLIPPNING (pulserande cirkel)
    ctx.beginPath();
    ctx.arc(canvas.width / 2, canvas.height / 2, clipRadius, 0, Math.PI * 2);
    ctx.clip();

    // Uppdatera radie för puls-effekt
    if (clipGrowing) {
      clipRadius += 1.5;  // Hastighet uppåt
      if (clipRadius >= maxClipRadius) clipGrowing = false;
    } else {
      clipRadius -= 1.5;  // Hastighet nedåt
      if (clipRadius <= 0) clipGrowing = true;
    }
    // ────────────────────────────────────────────────

    // Centrera och rotera amöban
    ctx.translate(canvas.width / 2, canvas.height / 2);
    ctx.rotate(angle);
    const scale = 0.4;
    ctx.scale(scale, scale);

    // Vektorgrafik från konverteraren (Amoebaface_Pink.svg)
    ctx.globalAlpha = 1.0;

    // Rosa kropp del 1
    ctx.fillStyle = '#875296';
    ctx.beginPath();
    ctx.moveTo(39, -69);
    ctx.bezierCurveTo(28, -75, 15, -78, 2, -78);
    ctx.bezierCurveTo(-42, -78, -77, -43, -77, 0);
    ctx.bezierCurveTo(-77, 34, -56, 62, -27, 73);
    ctx.bezierCurveTo(-34, 60, -38, 44, -38, 27);
    ctx.bezierCurveTo(-38, -21, -5, -61, 39, -69);
    ctx.lineTo(39, -69);
    ctx.fill();

    // Rosa kropp del 2
    ctx.fillStyle = '#FF8BEE';
    ctx.beginPath();
    ctx.moveTo(81, 0);
    ctx.bezierCurveTo(81, -30, 64, -56, 39, -69);
    ctx.bezierCurveTo(-5, -61, -38, -21, -38, 27);
    ctx.bezierCurveTo(-38, 44, -34, 60, -27, 73);
    ctx.bezierCurveTo(-18, 77, -8, 79, 2, 79);
    ctx.bezierCurveTo(45, 79, 81, 44, 81, 0);
    ctx.lineTo(81, 0);
    ctx.fill();

    // Svart ögonkant
    ctx.fillStyle = '#000';
    ctx.beginPath();
    ctx.moveTo(65, -23);
    ctx.bezierCurveTo(57, -51, 25, -69, 7, -59);
    ctx.bezierCurveTo(-3, -54, -8, -45, -9, -32);
    ctx.bezierCurveTo(-9, -32, -9, -31, -9, -31);
    ctx.bezierCurveTo(-13, -41, -20, -48, -28, -49);
    ctx.bezierCurveTo(-42, -51, -55, -40, -62, -18);
    ctx.bezierCurveTo(-69, 5, -60, 28, -44, 31);
    ctx.bezierCurveTo(-30, 34, -16, 22, -8, 3);
    ctx.bezierCurveTo(-7, -0, -6, -4, -5, -8);
    ctx.bezierCurveTo(-5, -7, -4, -5, -4, -3);
    ctx.bezierCurveTo(6, 24, 32, 39, 51, 30);
    ctx.bezierCurveTo(66, 23, 72, 1, 65, -23);
    ctx.lineTo(65, -23);
    ctx.fill();

    // Vita detaljer del 1
    ctx.fillStyle = '#FFFFFF';
    ctx.beginPath();
    ctx.moveTo(-61, 49);
    ctx.bezierCurveTo(-69, 46, -52, 68, -42, 66);
    ctx.bezierCurveTo(27, 50, 27, 50, 49, 60);
    ctx.bezierCurveTo(54, 63, 91, 40, 70, 38);
    ctx.bezierCurveTo(49, 36, 16, 35, 10, 37);
    ctx.bezierCurveTo(4, 38, -57, 51, -61, 49);
    ctx.lineTo(-61, 49);
    ctx.fill();

    // Mörkgrön/svart detalj (mun/skugga)
    ctx.fillStyle = '#02190D';
    ctx.beginPath();
    ctx.moveTo(90, 6);
    ctx.bezierCurveTo(93, -7, 86, -24, 81, -35);
    ctx.bezierCurveTo(73, -53, 62, -67, 46, -78);
    ctx.bezierCurveTo(46, -84, 35, -87, 27, -86);
    ctx.bezierCurveTo(19, -78, -1, -86, -11, -85);
    ctx.bezierCurveTo(-24, -85, -39, -75, -49, -67);
    ctx.bezierCurveTo(-66, -55, -78, -40, -84, -20);
    ctx.bezierCurveTo(-89, -19, -93, -8, -88, -2);
    ctx.bezierCurveTo(-87, 11, -88, 25, -81, 38);
    ctx.bezierCurveTo(-74, 51, -58, 57, -52, 70);
    ctx.bezierCurveTo(-49, 76, -36, 79, -29, 81);
    ctx.bezierCurveTo(-0, 93, 30, 82, 57, 73);
    ctx.bezierCurveTo(69, 66, 81, 48, 82, 34);
    ctx.bezierCurveTo(79, 31, 87, 19, 90, 16);
    ctx.bezierCurveTo(90, 15, 89, 9, 90, 6);
    ctx.lineTo(90, 6);

    ctx.moveTo(-72, -3);
    ctx.bezierCurveTo(-75, -14, -72, -24, -66, -34);
    ctx.bezierCurveTo(-56, -52, -28, -75, -9, -74);
    ctx.bezierCurveTo(-8, -74, 0, -72, 3, -72);
    ctx.bezierCurveTo(6, -72, 13, -76, 14, -75);
    ctx.bezierCurveTo(15, -77, 32, -70, 35, -69);
    ctx.bezierCurveTo(54, -62, 71, -37, 76, -17);
    ctx.bezierCurveTo(77, -10, 79, -3, 79, 4);
    ctx.bezierCurveTo(79, 5, 77, 22, 75, 19);
    ctx.bezierCurveTo(69, 23, 66, 28, 65, 30);
    ctx.bezierCurveTo(51, 34, 21, 32, 11, 34);
    ctx.bezierCurveTo(1, 36, -43, 48, -51, 46);
    ctx.bezierCurveTo(-55, 46, -58, 47, -61, 45);
    ctx.bezierCurveTo(-64, 42, -66, 39, -68, 35);
    ctx.bezierCurveTo(-68, 33, -67, 32, -66, 31);
    ctx.bezierCurveTo(-66, 17, -70, 13, -70, 8);
    ctx.bezierCurveTo(-74, 5, -70, 2, -72, -3);
    ctx.lineTo(-72, -3);

    ctx.moveTo(22, 51);
    ctx.bezierCurveTo(19, 51, 15, 51, 12, 51);
    ctx.bezierCurveTo(13, 47, 14, 42, 15, 40);
    ctx.bezierCurveTo(17, 39, 18, 39, 20, 39);
    ctx.bezierCurveTo(27, 39, 32, 39, 37, 40);
    ctx.bezierCurveTo(37, 44, 35, 50, 34, 53);
    ctx.lineTo(22, 51);

    ctx.moveTo(-22, 57);
    ctx.bezierCurveTo(-23, 53, -23, 50, -23, 47);
    ctx.bezierCurveTo(-8, 43, 0, 41, 9, 40);
    ctx.bezierCurveTo(9, 41, 10, 46, 10, 51);
    ctx.bezierCurveTo(0, 52, -15, 55, -22, 57);
    ctx.lineTo(-22, 57);

    ctx.moveTo(-40, 65);
    ctx.bezierCurveTo(-45, 62, -49, 59, -53, 54);
    ctx.bezierCurveTo(-42, 52, -37, 50, -28, 48);
    ctx.bezierCurveTo(-26, 51, -25, 55, -24, 58);
    ctx.bezierCurveTo(-35, 61, -52, 69, -27, 64);
    ctx.bezierCurveTo(-1, 60, 28, 60, 35, 60);
    ctx.bezierCurveTo(39, 64, 34, 66, 28, 70);
    ctx.bezierCurveTo(12, 81, -25, 75, -40, 65);
    ctx.lineTo(-40, 65);

    ctx.moveTo(56, 52);
    ctx.bezierCurveTo(54, 54, 51, 56, 49, 58);
    ctx.bezierCurveTo(47, 56, 43, 55, 39, 54);
    ctx.bezierCurveTo(39, 50, 40, 43, 42, 40);
    ctx.bezierCurveTo(48, 41, 54, 42, 62, 41);
    ctx.bezierCurveTo(61, 43, 53, 50, 56, 52);
    ctx.lineTo(56, 52);
    ctx.fill();

    // Vita ögonvitor och övriga vita detaljer
    ctx.fillStyle = '#FFFFFF';
    ctx.beginPath();
    ctx.moveTo(61, -21);
    ctx.bezierCurveTo(53, -43, 24, -62, 9, -53);
    ctx.bezierCurveTo(0, -48, -5, -40, -5, -29);
    ctx.bezierCurveTo(-6, -23, -4, -15, -2, -7);
    ctx.bezierCurveTo(7, 17, 29, 31, 46, 25);
    ctx.bezierCurveTo(62, 19, 66, -7, 61, -21);
    ctx.lineTo(61, -21);
    ctx.moveTo(40, 1);
    ctx.bezierCurveTo(30, 7, 16, 1, 10, -12);
    ctx.bezierCurveTo(24, -49, 41, -44, 47, -31);
    ctx.bezierCurveTo(54, -19, 51, -4, 40, 1);
    ctx.lineTo(40, 1);
    ctx.fill();

    ctx.fillStyle = '#FFFFFF';
    ctx.beginPath();
    ctx.moveTo(-28, -44);
    ctx.bezierCurveTo(-40, -46, -51, -37, -57, -18);
    ctx.bezierCurveTo(-63, 1, -57, 21, -43, 25);
    ctx.bezierCurveTo(-29, 29, -14, 15, -10, 3);
    ctx.bezierCurveTo(-4, -16, -13, -42, -28, -44);
    ctx.lineTo(-28, -44);
    ctx.moveTo(-19, -11);
    ctx.bezierCurveTo(-21, 1, -30, 9, -39, 8);
    ctx.bezierCurveTo(-48, 6, -54, -5, -52, -17);
    ctx.bezierCurveTo(-37, -29, -40, -31, -44, -31);
    ctx.bezierCurveTo(-25, -35, -17, -23, -19, -11);
    ctx.lineTo(-19, -11);
    ctx.fill();

    ctx.restore();

    angle += 0.02;
    requestAnimationFrame(draw);
  }

  draw();

  // Klickdetektering (cirkel runt mitten)
  canvas.addEventListener("click", (e) => {
    const rect = canvas.getBoundingClientRect();
    const x = e.clientX - rect.left - canvas.width / 2;
    const y = e.clientY - rect.top - canvas.height / 2;
    const radius = 120;
    if (x * x + y * y < radius * radius) {
      alert("🌀 Du klickade på amöban!");
    }
  });
}

/* =============================
   INIT APP
============================= */
updateLoginUI();
loadResources();
renderRegisteredParticipants();
updateSearchResults();