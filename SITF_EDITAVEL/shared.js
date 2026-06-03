// ── SITF shared.js — versão com API PHP + MySQL ──────────────────

const API = 'api';

// ── SCROLL PROGRESS ──────────────────────────────────────────────
window.addEventListener('scroll', () => {
  const el = document.getElementById('scrollBar');
  if (!el) return;
  const total = document.documentElement.scrollHeight - document.documentElement.clientHeight;
  el.style.width = (window.scrollY / total * 100) + '%';
});

// ── MOBILE DRAWER ────────────────────────────────────────────────
function toggleDrawer() {
  const d = document.getElementById('mobileDrawer');
  if (d) d.classList.toggle('open');
}

// ── TOAST ────────────────────────────────────────────────────────
let _toastTimer;
function showToast(msg, type = 'info') {
  const icons = { success: '✅', error: '❌', info: 'ℹ️', warn: '⚠️' };
  const t = document.getElementById('toast');
  if (!t) return;
  document.getElementById('toastIcon').textContent = icons[type] || 'ℹ️';
  document.getElementById('toastMsg').textContent  = msg;
  t.classList.add('show');
  clearTimeout(_toastTimer);
  _toastTimer = setTimeout(() => t.classList.remove('show'), 3500);
}

// ── SESSÃO DO USUÁRIO (localStorage só para sessão) ──────────────
const SITF = {
  getUser:  () => { try { return JSON.parse(localStorage.getItem('sitf_user') || 'null'); } catch { return null; } },
  setUser:  (u) => localStorage.setItem('sitf_user', JSON.stringify(u)),
  clearUser:() => localStorage.removeItem('sitf_user'),

  // ── API CALLS ────────────────────────────────────────────────

  // USUÁRIOS
  getUsers: async (tipo = null) => {
    const url = tipo ? `${API}/usuarios.php?tipo=${tipo}` : `${API}/usuarios.php`;
    const res = await fetch(url);
    return res.json();
  },

  getUsuario: async (id) => {
    const res = await fetch(`${API}/usuarios.php?id=${id}`);
    return res.json();
  },

  updateUsuario: async (dados) => {
    const res = await fetch(`${API}/usuarios.php`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(dados)
    });
    return res.json();
  },

  // VAGAS
  getVagas: async () => {
    const res = await fetch(`${API}/vagas.php`);
    return res.json();
  },

  createVaga: async (dados) => {
    const res = await fetch(`${API}/vagas.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(dados)
    });
    return res.json();
  },

  updateVaga: async (dados) => {
    const res = await fetch(`${API}/vagas.php`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(dados)
    });
    return res.json();
  },

  deleteVaga: async (id) => {
    const res = await fetch(`${API}/vagas.php?id=${id}`, { method: 'DELETE' });
    return res.json();
  },

  // CANDIDATURAS
  getCandidaturas: async (filtro = {}) => {
    let url = `${API}/candidaturas.php?`;
    if (filtro.vaga_id)       url += `vaga_id=${filtro.vaga_id}`;
    if (filtro.freelancer_id) url += `freelancer_id=${filtro.freelancer_id}`;
    const res = await fetch(url);
    return res.json();
  },

  createCandidatura: async (dados) => {
    const res = await fetch(`${API}/candidaturas.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(dados)
    });
    return res.json();
  },

  updateCandidatura: async (dados) => {
    const res = await fetch(`${API}/candidaturas.php`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(dados)
    });
    return res.json();
  },

  // AVALIAÇÕES
  getAvaliacoes: async (filtro = {}) => {
    let url = `${API}/avaliacoes.php?`;
    if (filtro.avaliado_id) url += `avaliado_id=${filtro.avaliado_id}&`;
    if (filtro.tipo)        url += `tipo=${filtro.tipo}`;
    const res = await fetch(url);
    return res.json();
  },

  createAvaliacao: async (dados) => {
    const res = await fetch(`${API}/avaliacoes.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(dados)
    });
    return res.json();
  },

  // MENSAGENS
  getMsgs: async (chatKey) => {
    const res = await fetch(`${API}/mensagens.php?chat_key=${chatKey}`);
    return res.json();
  },

  sendMsg: async (dados) => {
    const res = await fetch(`${API}/mensagens.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(dados)
    });
    return res.json();
  },
};

// ── AUTH MODAL ───────────────────────────────────────────────────
function openModal(type) {
  const m = document.getElementById('modalAuth');
  if (!m) return;
  m.classList.add('open');
  if (type === 'register') switchAuthTab('register');
  else switchAuthTab('login');
}

function closeModal() {
  const m = document.getElementById('modalAuth');
  if (m) m.classList.remove('open');
}

function switchAuthTab(tab) {
  ['login','register'].forEach(t => {
    const btn = document.getElementById('authTab_' + t);
    const frm = document.getElementById('authForm_' + t);
    if (btn) btn.classList.toggle('active', t === tab);
    if (frm) frm.classList.toggle('hidden', t !== tab);
  });
  const title = document.getElementById('modalAuthTitle');
  if (title) title.textContent = tab === 'login' ? 'Entrar no SITF' : 'Criar conta grátis';
}

document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-backdrop')) {
    document.querySelectorAll('.modal-backdrop.open').forEach(m => m.classList.remove('open'));
  }
});

// ── LOGIN ────────────────────────────────────────────────────────
async function doLogin() {
  const email = document.getElementById('loginEmail')?.value.trim();
  const senha = document.getElementById('loginSenha')?.value.trim();
  if (!email || !senha) { showToast('Preencha e-mail e senha.', 'warn'); return; }

  try {
    const res  = await fetch(`${API}/login.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, senha })
    });
    const data = await res.json();
    if (!res.ok || data.erro) { showToast(data.erro || 'Erro ao entrar.', 'error'); return; }

    SITF.setUser(data.usuario);
    closeModal();
    showToast(`Bem-vindo de volta, ${data.usuario.nome}! 👋`, 'success');
    setTimeout(() => {
      window.location.href = data.usuario.tipo === 'empregador' ? 'empregador.html' : 'freelancer.html';
    }, 1000);
  } catch (e) {
    showToast('Erro de conexão com o servidor.', 'error');
  }
}

// ── CADASTRO ─────────────────────────────────────────────────────
async function doRegister() {
  const nome      = document.getElementById('regNome')?.value.trim();
  const sobrenome = document.getElementById('regSobrenome')?.value.trim();
  const email     = document.getElementById('regEmail')?.value.trim();
  const tipo      = document.getElementById('regTipo')?.value;
  const senha     = document.getElementById('regSenha')?.value;

  if (!nome || !email || !senha) { showToast('Preencha todos os campos.', 'warn'); return; }
  if (senha.length < 6) { showToast('Senha deve ter pelo menos 6 caracteres.', 'warn'); return; }

  try {
    const res  = await fetch(`${API}/register.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ nome: nome + ' ' + sobrenome, email, senha, tipo })
    });
    const data = await res.json();
    if (!res.ok || data.erro) { showToast(data.erro || 'Erro ao cadastrar.', 'error'); return; }

    SITF.setUser(data.usuario);
    closeModal();
    showToast('Conta criada com sucesso! Bem-vindo ao SITF 🎉', 'success');
    setTimeout(() => {
      window.location.href = tipo === 'empregador' ? 'empregador.html' : 'freelancer.html';
    }, 1000);
  } catch (e) {
    showToast('Erro de conexão com o servidor.', 'error');
  }
}

// ── LOGOUT ───────────────────────────────────────────────────────
function logout() {
  SITF.clearUser();
  window.location.href = 'index.html';
}

// ── UTILITÁRIOS ──────────────────────────────────────────────────
function avatarColor(str) {
  const colors = ['#7c3aed','#2563eb','#059669','#dc2626','#d97706','#0891b2','#be185d','#4f46e5'];
  let h = 0;
  for (let i = 0; i < str.length; i++) h = str.charCodeAt(i) + ((h << 5) - h);
  return colors[Math.abs(h) % colors.length];
}

function initials(name) {
  return name.split(' ').slice(0,2).map(n => n[0]).join('').toUpperCase();
}

function avatarHTML(name, size = 'md', extra = '') {
  const col = avatarColor(name);
  return `<div class="avatar avatar-${size}" style="background:${col}" ${extra}>${initials(name)}</div>`;
}

function starsHTML(rating, max = 5) {
  let h = '';
  for (let i = 1; i <= max; i++) {
    h += `<span class="${i <= Math.round(rating) ? 'stars' : 'stars-empty'}">★</span>`;
  }
  return h;
}

function timeAgo(ts) {
  const date = new Date(ts);
  const diff = Date.now() - date.getTime();
  const m = Math.floor(diff / 60000);
  const h = Math.floor(m / 60);
  const d = Math.floor(h / 24);
  if (m < 1)  return 'agora';
  if (m < 60) return `há ${m} min`;
  if (h < 24) return `há ${h}h`;
  if (d < 30) return `há ${d} dia${d > 1 ? 's' : ''}`;
  return date.toLocaleDateString('pt-BR');
}

function buildAuthModal() {
  return `
  <div class="modal-backdrop" id="modalAuth">
    <div class="modal-box">
      <div class="modal-header">
        <div>
          <h3 class="modal-title" id="modalAuthTitle">Entrar no SITF</h3>
          <p class="modal-subtitle">Conecte. Trabalhe. Conquiste.</p>
        </div>
        <button class="modal-close" onclick="closeModal()">✕</button>
      </div>
      <div class="tabs mb-2">
        <button class="tab-btn active" id="authTab_login" onclick="switchAuthTab('login')">Entrar</button>
        <button class="tab-btn" id="authTab_register" onclick="switchAuthTab('register')">Cadastrar</button>
      </div>
      <div id="authForm_login">
        <div class="form-group">
          <label class="form-label">E-mail</label>
          <input class="form-control" type="email" id="loginEmail" placeholder="seu@email.com">
        </div>
        <div class="form-group">
          <label class="form-label">Senha</label>
          <input class="form-control" type="password" id="loginSenha" placeholder="••••••••">
        </div>
        <button class="btn btn-primary btn-full btn-lg" onclick="doLogin()">Entrar na plataforma →</button>
        <div style="text-align:center;margin-top:1rem;font-size:0.82rem;color:var(--text-muted)">
          Demo: <strong>ana@email.com / 123456</strong> (freelancer) · <strong>tech@start.com / 123456</strong> (empregador)
        </div>
      </div>
      <div id="authForm_register" class="hidden">
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">Nome</label>
            <input class="form-control" type="text" id="regNome" placeholder="João">
          </div>
          <div class="form-group">
            <label class="form-label">Sobrenome</label>
            <input class="form-control" type="text" id="regSobrenome" placeholder="Silva">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">E-mail</label>
          <input class="form-control" type="email" id="regEmail" placeholder="seu@email.com">
        </div>
        <div class="form-group">
          <label class="form-label">Você é...</label>
          <select class="form-control" id="regTipo">
            <option value="freelancer">Freelancer — busco trabalho</option>
            <option value="empregador">Empregador — ofereço vagas</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Senha</label>
          <input class="form-control" type="password" id="regSenha" placeholder="Mínimo 6 caracteres">
        </div>
        <button class="btn btn-primary btn-full btn-lg" onclick="doRegister()">Criar conta grátis 🎉</button>
      </div>
    </div>
  </div>`;
}

// ── NOTIFICAÇÕES ─────────────────────────────────────────────────
async function renderNavBadge() {
  const user = SITF.getUser();
  if (!user) return;

  try {
    let total = 0;
    if (user.tipo === 'empregador') {
      const vagas = await SITF.getVagas();
      const minhasVagas = vagas.filter(v => v.empregador_id === user.id).map(v => v.id);
      if (minhasVagas.length) {
        const cands = await SITF.getCandidaturas({ vaga_id: minhasVagas[0] });
        total = Array.isArray(cands) ? cands.filter(c => c.status === 'pendente').length : 0;
      }
    } else {
      const cands = await SITF.getCandidaturas({ freelancer_id: user.id });
      total = Array.isArray(cands) ? cands.filter(c => c.status === 'aprovado' && !c.visto).length : 0;
    }

    const existing = document.getElementById('navNotifBadge');
    if (existing) existing.remove();
    if (total === 0) return;

    const dashLink = document.querySelector(
      `a[href="${user.tipo === 'empregador' ? 'empregador' : 'freelancer'}.html"]`
    );
    if (!dashLink) return;

    const badge = document.createElement('span');
    badge.id = 'navNotifBadge';
    badge.textContent = total > 9 ? '9+' : total;
    badge.style.cssText = `
      display:inline-flex;align-items:center;justify-content:center;
      background:linear-gradient(135deg,#7c3aed,#2563eb);
      color:#fff;font-size:0.6rem;font-weight:700;
      width:16px;height:16px;border-radius:50%;
      margin-left:4px;vertical-align:middle;
    `;
    dashLink.appendChild(badge);
  } catch (e) {
    console.log('Notif error:', e);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  renderNavBadge();
  setInterval(renderNavBadge, 30000);
});


// ── HISTÓRICO ────────────────────────────────────────────────────
SITF.getHistorico = async (filtro = {}) => {
  let url = 'api/historico.php?';
  if (filtro.freelancer_id) url += `freelancer_id=${filtro.freelancer_id}`;
  if (filtro.empregador_id) url += `empregador_id=${filtro.empregador_id}`;
  const res = await fetch(url);
  return res.json();
};

SITF.createHistorico = async (dados) => {
  const res = await fetch('api/historico.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(dados)
  });
  return res.json();
};

SITF.updateHistorico = async (dados) => {
  const res = await fetch('api/historico.php', {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(dados)
  });
  return res.json();
};

SITF.deleteHistorico = async (id) => {
  const res = await fetch(`api/historico.php?id=${id}`, { method: 'DELETE' });
  return res.json();
};