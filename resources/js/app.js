// Progressive enhancement: native forms still work without JavaScript.
const dfThemeKey = 'devflow.theme';
function dfSyncThemeToggle() {
    const isDark = document.documentElement.dataset.theme === 'dark';
    document.querySelector('meta[name="theme-color"]')?.setAttribute('content', isDark ? '#1b181c' : '#f6f1ea');
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-label', isDark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap');
        button.setAttribute('aria-pressed', String(isDark));
        const label = button.querySelector('[data-theme-label]');
        if (label) label.textContent = isDark ? 'Tema terang' : 'Tema gelap';
        const icon = button.querySelector('[data-theme-icon]');
        icon?.classList.toggle('fa-moon', !isDark);
        icon?.classList.toggle('fa-sun', isDark);
    });
}
dfSyncThemeToggle();
document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const theme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
        document.documentElement.dataset.theme = theme;
        try { localStorage.setItem(dfThemeKey, theme); } catch {}
        dfSyncThemeToggle();
    });
});

document.querySelectorAll('[data-toggle-password]').forEach((button) => {
    button.addEventListener('click', () => {
        const field = document.getElementById(button.getAttribute('aria-controls'));
        if (!field) return;
        const reveal = field.type === 'password';
        field.type = reveal ? 'text' : 'password';
        button.setAttribute('aria-label', reveal ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        button.setAttribute('title', reveal ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        button.setAttribute('aria-pressed', String(reveal));
        const icon = button.querySelector('[data-password-icon]');
        icon?.classList.toggle('fa-eye', !reveal);
        icon?.classList.toggle('fa-eye-slash', reveal);
    });
});
const password = document.querySelector('#password');
const confirmation = document.querySelector('#password_confirmation');
const matchHint = document.querySelector('[data-password-match]');
function updateMatch() {
    if (!matchHint || !password || !confirmation) return;
    if (!confirmation.value) { confirmation.setCustomValidity(''); matchHint.textContent = 'Ulangi kata sandi untuk konfirmasi.'; matchHint.className = 'mt-1.5 text-xs text-zinc-500'; return; }
    const matches = password.value === confirmation.value;
    matchHint.textContent = matches ? 'Kata sandi sudah cocok.' : 'Kata sandi belum cocok.';
    matchHint.className = 'mt-1.5 text-xs ' + (matches ? 'text-green-700' : 'text-red-600');
    confirmation.setCustomValidity(matches ? '' : 'Kata sandi harus sama.');
}
password?.addEventListener('input', updateMatch);
confirmation?.addEventListener('input', updateMatch);
const createDialogs = [...document.querySelectorAll('[data-create-dialog]')];
const openCreateDialog = (type) => {
  const dialog = createDialogs.find((candidate) => candidate.dataset.createType === type);
  if (dialog && !dialog.open) dialog.showModal();
};
document.querySelectorAll('[data-create-open]').forEach((button) => {
  button.addEventListener('click', () => {
    openCreateDialog(button.dataset.createOpen);
    if (!button.dataset.routineTemplate) return;
    const form = createDialogs.find((dialog) => dialog.dataset.createType === 'routine')?.querySelector('form');
    if (!form) return;
    try {
      const template = JSON.parse(button.dataset.routineTemplate);
      for (const [name, value] of Object.entries({ title: template.title, frequency: template.frequency, target_per_week: template.target, estimated_minutes: template.minutes, minimum_action: template.minimum, normal_action: template.normal, extended_action: template.extended })) {
        const field = form.elements.namedItem(name);
        if (field) field.value = value;
      }
      form.querySelectorAll('input[name="days_of_week[]"]').forEach((field) => {
        field.checked = template.days.includes(Number(field.value));
      });
    } catch (error) {
      console.error('DevFlow routine template could not be loaded:', error);
    }
  });
});
createDialogs.forEach((dialog) => {
  dialog.querySelector('[data-create-close]')?.addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) dialog.close();
  });
  if (dialog.hasAttribute('data-create-errors')) dialog.showModal();
});
window.addEventListener('pageshow', (event) => {
  if (event.persisted) createDialogs.forEach((dialog) => { if (dialog.open) dialog.close(); });
});
const createType = new URLSearchParams(window.location.search).get('quick_add') || new URLSearchParams(window.location.search).get('create');
if (createType) openCreateDialog(createType);
const confirmDialog = document.createElement('dialog');
confirmDialog.className = 'fixed inset-0 m-auto max-h-[calc(100dvh-32px)] w-[min(520px,calc(100vw-32px))] max-w-[calc(100vw-32px)] overflow-y-auto overscroll-contain rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl';
confirmDialog.setAttribute('data-confirm-dialog', '');
confirmDialog.setAttribute('aria-labelledby', 'confirmation-title');
confirmDialog.setAttribute('aria-describedby', 'confirmation-message');
confirmDialog.innerHTML = '<h2 id="confirmation-title" class="text-lg font-semibold">Konfirmasi tindakan</h2><p id="confirmation-message" data-dialog-message class="mt-2 text-sm leading-6 text-zinc-600 [overflow-wrap:anywhere]"></p><div class="mt-6 flex flex-wrap justify-end gap-2"><button type="button" data-dialog-cancel class="min-h-11 rounded-lg border border-zinc-200 px-4 py-2 text-sm">Batal</button><button type="button" data-dialog-confirm class="min-h-11 rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white">Lanjutkan</button></div>';
if (document.querySelector('form[data-confirm]')) {
  document.body.append(confirmDialog);
  const confirmedForms = new WeakSet();
  let pendingForm = null;
  let pendingSubmitter = null;
  let previousOverflow = '';
  const clearConfirmation = (restoreFocus = true) => {
    document.body.style.overflow = previousOverflow;
    if (restoreFocus) pendingSubmitter?.focus({ preventScroll: true });
    pendingForm = null;
    pendingSubmitter = null;
  };
  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (confirmedForms.has(form)) return;
      event.preventDefault();
      if (confirmDialog.open) return;
      pendingForm = form;
      pendingSubmitter = event.submitter;
      confirmDialog.querySelector('[data-dialog-message]').textContent = form.dataset.confirm;
      previousOverflow = document.body.style.overflow;
      document.body.style.overflow = 'hidden';
      confirmDialog.showModal();
      confirmDialog.querySelector('[data-dialog-cancel]').focus();
    });
  });
  confirmDialog.querySelector('[data-dialog-cancel]').addEventListener('click', () => {
    confirmDialog.close();
    clearConfirmation();
  });
  confirmDialog.addEventListener('close', () => {
    if (!confirmDialog.open && pendingForm) clearConfirmation();
  });
  confirmDialog.querySelector('[data-dialog-confirm]').addEventListener('click', () => {
    const form = pendingForm;
    const submitter = pendingSubmitter;
    if (!form) return;
    confirmDialog.close();
    clearConfirmation(false);
    confirmedForms.add(form);
    try {
      if (submitter && form.contains(submitter)) form.requestSubmit(submitter);
      else form.requestSubmit();
    } finally {
      confirmedForms.delete(form);
    }
  });
  window.addEventListener('pageshow', (event) => {
    if (event.persisted && pendingForm) {
      confirmDialog.close();
      clearConfirmation();
    }
  });
}

// Keep native submit controls enabled during form submission.
// Disabling the submitter synchronously inside `submit` can prevent a form
// navigation in some browsers. Server-side validation remains authoritative.

// Installable PWA: register only on secure origins; no authenticated content is cached.
if ('serviceWorker' in navigator && (window.isSecureContext || location.hostname === 'localhost')) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {
            // Offline availability is optional; normal navigation continues to work.
        });
    });
}

// Command palette (progressive enhancement; links always navigate normally).
const navigationProgress = document.getElementById('df-navigation-progress');
document.addEventListener('click', (event) => {
  if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
  const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
  if (!link || link.target || link.hasAttribute('download')) return;
  const destination = new URL(link.href, window.location.href);
  if (destination.origin !== window.location.origin || destination.hash || (destination.pathname === window.location.pathname && destination.search === window.location.search)) return;
  navigationProgress?.classList.add('is-loading');
}, { capture: true });
window.addEventListener('pageshow', () => {
  navigationProgress?.classList.remove('is-loading');
});

const dfPalette = document.getElementById('df-command');
const dfSearch = document.querySelector('[data-command-input]');
const dfItems = [...document.querySelectorAll('[data-command-item]')];
const dfTaskSearchItem = document.querySelector('[data-command-task-search]');
const dfTaskSearchLabel = document.querySelector('[data-command-task-label]');
const dfNavActionsMenu = document.querySelector('[data-nav-actions-menu]');
let dfActive = 0;
function dfVisibleItems(){return dfItems.filter(item=>!item.hidden)}
function dfHighlight(){dfVisibleItems().forEach((item,index)=>item.toggleAttribute('data-active',index===dfActive));}
function dfOpen(){if(!dfPalette || dfPalette.open)return;dfNavActionsMenu?.removeAttribute('open');dfPalette.showModal();dfSearch.value='';dfItems.forEach(item=>item.hidden=item===dfTaskSearchItem);document.querySelector('[data-command-empty]').hidden=true;dfActive=0;dfHighlight();dfSearch.focus()}
document.querySelectorAll('[data-command-open]').forEach(button=>button.addEventListener('click',dfOpen));
document.addEventListener('click',event=>{if(dfNavActionsMenu?.open&&!dfNavActionsMenu.contains(event.target))dfNavActionsMenu.open=false});
dfNavActionsMenu?.addEventListener('keydown',event=>{if(event.key==='Escape'&&dfNavActionsMenu.open){dfNavActionsMenu.open=false;dfNavActionsMenu.querySelector('summary')?.focus();event.stopPropagation()}});
document.addEventListener('keydown',event=>{if((event.ctrlKey||event.metaKey)&&event.key.toLowerCase()==='k'){event.preventDefault();dfPalette?.open?dfPalette.close():dfOpen()}});
dfSearch?.addEventListener('input',()=>{const term=dfSearch.value.trim();const normalizedTerm=term.toLowerCase();dfItems.forEach(item=>item.hidden=item===dfTaskSearchItem||!item.textContent.toLowerCase().includes(normalizedTerm));const routeMatches=dfVisibleItems().length>0;if(dfTaskSearchItem){dfTaskSearchItem.hidden=term.length===0||routeMatches;if(term){const taskUrl=new URL(dfTaskSearchItem.dataset.searchBase,window.location.origin);taskUrl.searchParams.set('q',term);dfTaskSearchItem.href=taskUrl.toString();dfTaskSearchLabel.textContent=`Cari tugas: ${term}`;}}dfActive=0;dfHighlight();document.querySelector('[data-command-empty]').hidden=dfVisibleItems().length>0});
dfSearch?.addEventListener('keydown',event=>{const visible=dfVisibleItems();if(event.key==='ArrowDown'){event.preventDefault();dfActive=Math.min(dfActive+1,visible.length-1);dfHighlight()}else if(event.key==='ArrowUp'){event.preventDefault();dfActive=Math.max(0,dfActive-1);dfHighlight()}else if(event.key==='Enter'&&visible[dfActive]){event.preventDefault();visible[dfActive].click()}});
dfPalette?.addEventListener('click',event=>{if(event.target===dfPalette)dfPalette.close()});
document.querySelectorAll('[data-note-autosave]').forEach((form) => {
 let timer;
 let saving = false;
 let dirty = false;
 const status = form.querySelector('[data-note-save-status]');
 const retryButton = form.querySelector('[data-note-save-retry]');
 const recoveryPanel = form.querySelector('[data-note-recovery]');
 const recoveryKey = `devflow:personal-note-draft:${form.dataset.noteOwner}:${form.dataset.noteId}`;
 const saveLocalDraft = () => {
   try {
     const fields = [...form.querySelectorAll('[data-note-field]')].map((field) => ({
       name: field.name,
       type: field.type,
       value: field.value,
       checked: field instanceof HTMLInputElement && field.type === 'checkbox' ? field.checked : null,
     }));
     sessionStorage.setItem(recoveryKey, JSON.stringify({ savedAt: Date.now(), fields }));
     if (recoveryPanel) recoveryPanel.hidden = false;
     return true;
   } catch (error) {
     console.warn('DevFlow note recovery draft could not be stored in this tab:', error);
     return false;
   }
 };
 const clearLocalDraft = () => {
   try {
     sessionStorage.removeItem(recoveryKey);
   } catch (error) {
     console.warn('DevFlow note recovery draft could not be cleared:', error);
   }
   if (recoveryPanel) recoveryPanel.hidden = true;
 };
 try {
   if (sessionStorage.getItem(recoveryKey) && recoveryPanel) recoveryPanel.hidden = false;
 } catch (error) {
   console.warn('DevFlow note recovery draft could not be read:', error);
 }
 const save = async () => {
   if (saving || !dirty) return;
   saving = true;
   dirty = false;
   if (retryButton) retryButton.disabled = true;
   if (status) status.textContent = 'Menyimpan…';
   try {
     const response = await fetch(form.action, {
       method: 'POST', credentials: 'same-origin', body: new FormData(form),
       headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
     });
     if (!response.ok) throw new Error(`HTTP ${response.status}`);
     if (response.redirected && new URL(response.url).pathname !== window.location.pathname) {
       throw new Error('Sesi login berakhir; simpan ulang setelah masuk kembali.');
     }
     if (status) status.textContent = `Tersimpan ${new Intl.DateTimeFormat('id', { hour: '2-digit', minute: '2-digit' }).format(new Date())}`;
     if (dirty) timer = window.setTimeout(save, 0);
     else clearLocalDraft();
   } catch (error) {
     dirty = true;
     const draftSaved = saveLocalDraft();
     if (status) status.textContent = draftSaved
       ? 'Belum tersimpan. Draf disimpan untuk pemulihan pada tab ini; periksa koneksi lalu coba lagi.'
       : 'Belum tersimpan. Teks tetap di form; periksa koneksi lalu coba lagi.';
     if (retryButton) retryButton.disabled = false;
     console.error('DevFlow note autosave failed:', error);
   } finally {
     saving = false;
   }
 };
 form.querySelectorAll('[data-note-field]').forEach((field) => {
   const scheduleSave = () => {
     dirty = true;
     if (status) status.textContent = 'Perubahan belum disimpan…';
     window.clearTimeout(timer);
     timer = window.setTimeout(save, 800);
   };
   field.addEventListener('input', scheduleSave);
   field.addEventListener('change', scheduleSave);
 });
 retryButton?.addEventListener('click', () => {
   window.clearTimeout(timer);
   dirty = true;
   save();
 });
 form.querySelector('[data-note-recovery-restore]')?.addEventListener('click', () => {
   try {
     const draft = JSON.parse(sessionStorage.getItem(recoveryKey) || '{}');
     if (!Array.isArray(draft.fields)) throw new Error('Invalid recovery draft.');
     draft.fields.forEach((savedField) => {
       const field = [...form.querySelectorAll('[data-note-field]')].find((candidate) => candidate.name === savedField.name && candidate.type === savedField.type);
       if (!field) return;
       field.value = savedField.value;
       if (field instanceof HTMLInputElement && field.type === 'checkbox' && savedField.checked !== null) field.checked = savedField.checked;
     });
     recoveryPanel.hidden = true;
     dirty = true;
     if (status) status.textContent = 'Draf dipulihkan. Menyimpan perubahan…';
     window.clearTimeout(timer);
     timer = window.setTimeout(save, 800);
   } catch (error) {
     if (status) status.textContent = 'Draf pemulihan tidak dapat dibaca. Isi form masih aman.';
     console.error('DevFlow note recovery draft could not be restored:', error);
   }
 });
 form.querySelector('[data-note-recovery-discard]')?.addEventListener('click', clearLocalDraft);
});
const dfRail=document.querySelector('[data-sidebar]');const dfScrim=document.querySelector('[data-sidebar-overlay]');
const dfRailOpeners=[...document.querySelectorAll('[data-sidebar-open]')];let dfRailLastOpener=null;
const dfRailMedia = window.matchMedia('(max-width: 850px)');
const dfStage = document.querySelector('.df-stage');
const dfSidebarCollapse = document.querySelector('[data-sidebar-collapse]');
const dfSidebarCollapseIcon = dfSidebarCollapse?.querySelector('[data-sidebar-collapse-icon]');
const dfSidebarCollapseKey = 'devflow.sidebar.collapsed';
let dfSidebarIsCollapsed = document.documentElement.dataset.sidebarCollapsed === 'true';
const dfRailScrollKey = 'devflow.sidebar.scrollTop';
const dfRailNavigation = dfRail?.querySelector('nav[aria-label="Navigasi utama"]');
const dfRailNavigationScrollKey = 'devflow.sidebar.navigation.scrollTop';
function dfSaveRailScroll() {
 if (!dfRail) return;
 try { sessionStorage.setItem(dfRailScrollKey, String(dfRail.scrollTop)); } catch {}
 try { if (dfRailNavigation) sessionStorage.setItem(dfRailNavigationScrollKey, String(dfRailNavigation.scrollTop)); } catch {}
}
function dfRestoreRailScroll() {
 if (!dfRail) return;
 try {
  const savedScrollTop = Number(sessionStorage.getItem(dfRailScrollKey));
  if (Number.isFinite(savedScrollTop) && savedScrollTop >= 0) dfRail.scrollTop = savedScrollTop;
 } catch {}
 try {
  const savedNavigationScrollTop = Number(sessionStorage.getItem(dfRailNavigationScrollKey));
  if (dfRailNavigation && Number.isFinite(savedNavigationScrollTop) && savedNavigationScrollTop >= 0) {
   dfRailNavigation.scrollTop = savedNavigationScrollTop;
  }
 } catch {}
}
dfRail?.addEventListener('scroll', dfSaveRailScroll, { passive: true });
dfRailNavigation?.addEventListener('scroll', dfSaveRailScroll, { passive: true });
window.addEventListener('pagehide', dfSaveRailScroll);
window.addEventListener('pageshow', () => window.requestAnimationFrame(dfRestoreRailScroll));
window.requestAnimationFrame(dfRestoreRailScroll);
function dfSyncSidebarCollapse() {
 if (!dfRail || !dfSidebarCollapse) return;
 dfRail.toggleAttribute('data-collapsed', dfSidebarIsCollapsed);
 dfSidebarCollapse.setAttribute('aria-pressed', String(dfSidebarIsCollapsed));
 dfSidebarCollapse.setAttribute('aria-label', dfSidebarIsCollapsed ? 'Perlebar sidebar' : 'Kecilkan sidebar');
 dfSidebarCollapse.setAttribute('title', dfSidebarIsCollapsed ? 'Perlebar sidebar' : 'Kecilkan sidebar');
 dfSidebarCollapseIcon?.classList.toggle('fa-angles-left', !dfSidebarIsCollapsed);
 dfSidebarCollapseIcon?.classList.toggle('fa-angles-right', dfSidebarIsCollapsed);
}
dfSyncSidebarCollapse();
dfSidebarCollapse?.addEventListener('click', () => {
 dfSidebarIsCollapsed = !dfSidebarIsCollapsed;
 document.documentElement.toggleAttribute('data-sidebar-collapsed', dfSidebarIsCollapsed);
 try { localStorage.setItem(dfSidebarCollapseKey, String(dfSidebarIsCollapsed)); } catch {}
 dfSyncSidebarCollapse();
});
function dfSyncRail(open) {
 if (!dfRail) return;
 const mobileOpen = dfRailMedia.matches && open;
 dfRail.inert = dfRailMedia.matches && !open;
 if (dfStage) dfStage.inert = mobileOpen;
 document.body.style.overflow = mobileOpen ? 'hidden' : '';
 dfRail.toggleAttribute('data-open', mobileOpen);
 if (dfScrim) dfScrim.hidden = !mobileOpen;
 dfRailOpeners.forEach((opener)=>opener.setAttribute('aria-expanded', String(mobileOpen)));
}
function dfSetRail(open) {
 if (open) dfRailLastOpener = document.activeElement instanceof HTMLElement ? document.activeElement : dfRailOpeners[0];
 dfSyncRail(open);
 if (open) document.querySelector('[data-sidebar-close]')?.focus();
 else (dfRailLastOpener ?? dfRailOpeners[0])?.focus();
}
dfSyncRail(false);
dfRailMedia.addEventListener('change', () => dfSyncRail(false));
dfRailOpeners.forEach((opener)=>opener.addEventListener('click',()=>dfSetRail(true)));document.querySelector('[data-sidebar-close]')?.addEventListener('click',()=>dfSetRail(false));dfScrim?.addEventListener('click',()=>dfSetRail(false));
document.addEventListener('keydown',event=>{
 if (!dfRail?.hasAttribute('data-open')) return;
 if (event.key === 'Escape') { dfSetRail(false); return; }
 if (event.key !== 'Tab') return;
 const focusable = [...dfRail.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])')].filter((element) => !element.hidden);
 const first = focusable[0]; const last = focusable.at(-1);
 if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
 else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
});
// Context navigation marks the currently selected section without mutating app data.
document.querySelectorAll('.df-project-nav a[href^="#"]').forEach(anchor=>anchor.addEventListener('click',()=>{document.querySelectorAll('.df-project-nav a').forEach(link=>link.removeAttribute('aria-current'));anchor.setAttribute('aria-current','location')}));

// Toasts are non-blocking; focus/hover pause auto dismissal.
function initializeToast(toast) {
  let timer;
  const dismiss = () => { toast.classList.add('is-hiding'); setTimeout(() => toast.remove(), 220); };
  const start = () => { const timeout = Number(toast.dataset.timeout); if (timeout > 0) timer = setTimeout(dismiss, timeout); };
  const stop = () => clearTimeout(timer);
  toast.addEventListener('mouseenter', stop);
  toast.addEventListener('mouseleave', start);
  toast.addEventListener('focusin', stop);
  toast.addEventListener('focusout', start);
  toast.querySelector('[data-dismiss-toast]')?.addEventListener('click', () => { stop(); dismiss(); });
  start();
}
document.querySelectorAll('[data-toast]').forEach(initializeToast);
const taskRoot = document.querySelector('[data-tasks-root]');
if (taskRoot) {
 const composer = taskRoot.querySelector('#new-task-panel');
 const opener = taskRoot.querySelector('[data-task-form-toggle]');
 const toggle = (open) => {
   if (!composer) return;
   composer.hidden = !open;
   opener?.setAttribute('aria-expanded', String(open));
   if (open) composer.querySelector('input[name="title"]')?.focus();
   else opener?.focus();
 };
 opener?.addEventListener('click', () => toggle(composer?.hidden));
 taskRoot.querySelector('[data-task-form-cancel]')?.addEventListener('click', () => toggle(false));
 let dragged = null;
 let moving = false;
 const showMoveError = (message) => {
   const stack = document.querySelector('[data-toast-stack]');
   if (!stack) return;
   stack.querySelector('[data-task-move-error]')?.remove();
   const template = document.querySelector('[data-error-toast-template]');
   if (!template) return;
   const toast = template.content.firstElementChild.cloneNode(true);
   toast.dataset.taskMoveError = '';
   toast.querySelector('[data-error-message]').textContent = message;
   stack.appendChild(toast);
   initializeToast(toast);
 };
 taskRoot.querySelectorAll('[data-task-card]').forEach((card) => {
   card.addEventListener('dragstart', (event) => {
     if (moving) { event.preventDefault(); return; }
     dragged = card;
     event.dataTransfer.effectAllowed = 'move';
     event.dataTransfer.setData('text/plain', card.dataset.taskId || '');
     card.classList.add('is-dragging');
   });
   card.addEventListener('dragend', () => {
     card.classList.remove('is-dragging');
     if (!moving) dragged = null;
     taskRoot.querySelectorAll('[data-task-dropzone]').forEach(zone => zone.classList.remove('is-over'));
   });
 });
 taskRoot.querySelectorAll('[data-task-dropzone]').forEach((zone) => {
   zone.addEventListener('dragover', (event) => {
     if (!dragged || moving) return;
     event.preventDefault();
     event.dataTransfer.dropEffect = 'move';
     zone.classList.add('is-over');
   });
   zone.addEventListener('dragleave', (event) => {
     if (!zone.contains(event.relatedTarget)) zone.classList.remove('is-over');
   });
   zone.addEventListener('drop', async (event) => {
     event.preventDefault();
     event.stopPropagation();
     zone.classList.remove('is-over');
     if (!dragged || moving) return;
     const card = dragged;
     dragged = null;
     const status = zone.dataset.taskDropzone;
     const select = card.querySelector('[data-task-status]');
     const currentStatus = card.dataset.taskStatus || select?.value;
     const currentColumn = card.closest('[data-task-column]')?.dataset.taskColumn;
     const nextColumn = zone.dataset.taskColumn;
     if (!status || currentStatus === status || (currentColumn && currentColumn === nextColumn)) return;
     const url = card.dataset.taskUpdateUrl;
     const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
     if (!url || !csrf) { showMoveError('Tugas gagal disimpan. Muat ulang halaman lalu coba lagi.'); return; }

     moving = true;
     const prior = card.parentElement;
     const next = card.nextSibling;
     zone.appendChild(card);
     try {
       const response = await fetch(url, {
         method: 'PATCH', credentials: 'same-origin', redirect: 'error',
         headers: {
           'X-CSRF-TOKEN': csrf,
           'X-Requested-With': 'XMLHttpRequest',
           'Accept': 'application/json',
           'Content-Type': 'application/json',
         },
         body: JSON.stringify({ status }),
       });
       const payload = await response.json().catch(() => null);
       if (!response.ok || !payload?.task || payload.task.status !== status) {
         const detail = payload?.message || `HTTP ${response.status}`;
         throw new Error(detail);
       }
       if (select) select.value = status;
       card.dataset.taskStatus = status;
       window.location.reload();
     } catch (error) {
       if (prior) prior.insertBefore(card, next && next.parentNode === prior ? next : null);
       console.error('DevFlow task move failed:', error);
       showMoveError('Tugas gagal dipindahkan. ' + (error?.message || 'Coba lagi.'));
     } finally {
       moving = false;
     }
   });
 });
}

document.querySelectorAll('[data-focus-sortable]').forEach((list) => {
 const feedback = list.parentElement.querySelector('[data-focus-order-feedback]');
 let dragged = null;
 const refreshRanks = () => list.querySelectorAll('[data-focus-task]').forEach((item, index) => {
   const rank = item.querySelector('[data-focus-rank]');
   if (rank) rank.textContent = `P${index + 1}`;
 });
 const persistOrder = async (previousOrder) => {
   refreshRanks();
   if (feedback) feedback.textContent = 'Menyimpan urutan…';
   try {
     const response = await fetch(list.dataset.focusReorderUrl, {
       method: 'POST', credentials: 'same-origin',
       headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/json' },
       body: JSON.stringify({ task_ids: [...list.querySelectorAll('[data-focus-task]')].map((item) => Number(item.dataset.focusTask)) }),
     });
     const result = await response.json().catch(() => null);
     if (!response.ok || !result?.message) throw new Error(result?.message || `HTTP ${response.status}`);
     if (feedback) feedback.textContent = 'Urutan tersimpan.';
   } catch (error) {
     const items = new Map([...list.querySelectorAll('[data-focus-task]')].map((item) => [item.dataset.focusTask, item]));
     previousOrder.forEach((id) => { const item = items.get(id); if (item) list.appendChild(item); });
     refreshRanks();
     if (feedback) feedback.textContent = `Urutan gagal disimpan. ${error?.message || 'Coba lagi.'}`;
   }
 };
 list.querySelectorAll('[data-focus-task]').forEach((item) => {
   item.addEventListener('dragstart', (event) => {
     dragged = item;
     event.dataTransfer.effectAllowed = 'move';
     item.classList.add('is-dragging');
   });
   item.addEventListener('dragend', () => { dragged = null; item.classList.remove('is-dragging'); });
   item.addEventListener('dragover', (event) => { if (dragged && dragged !== item) event.preventDefault(); });
   item.addEventListener('drop', (event) => {
     if (!dragged || dragged === item) return;
     event.preventDefault();
     const before = [...list.querySelectorAll('[data-focus-task]')].map((entry) => entry.dataset.focusTask);
     const bounds = item.getBoundingClientRect();
     list.insertBefore(dragged, event.clientY < bounds.top + bounds.height / 2 ? item : item.nextSibling);
     persistOrder(before);
   });
   item.querySelectorAll('[data-focus-move]').forEach((button) => button.addEventListener('click', () => {
     const before = [...list.querySelectorAll('[data-focus-task]')].map((entry) => entry.dataset.focusTask);
     const sibling = button.dataset.focusMove === 'up' ? item.previousElementSibling : item.nextElementSibling;
     if (!sibling) return;
     if (button.dataset.focusMove === 'up') list.insertBefore(item, sibling);
     else list.insertBefore(sibling, item);
     persistOrder(before);
   }));
 });
});

document.querySelectorAll('[data-weekly-select-all]').forEach((button) => {
 const checkboxes = [...button.closest('section')?.querySelectorAll('[data-weekly-task]') || []];
 button.addEventListener('click', () => {
   const shouldSelect = checkboxes.some((checkbox) => !checkbox.checked);
   checkboxes.forEach((checkbox) => { checkbox.checked = shouldSelect; });
   button.textContent = shouldSelect ? 'Batalkan pilihan' : 'Pilih semua';
 });
});

const workScheduleForm = document.querySelector('[data-work-schedule-preview]');
if (workScheduleForm) {
 const previewText = workScheduleForm.querySelector('[data-work-preview-text]');
 const previewDays = workScheduleForm.querySelector('[data-work-preview-days]');
 const labels = new Map([...workScheduleForm.querySelectorAll('input[name="working_days[]"]')].map((input) => [input.value, input.parentElement.textContent.trim()]));
 const updateWorkPreview = () => {
   const start = workScheduleForm.querySelector('[name="workday_start"]')?.value || '09:00';
   const end = workScheduleForm.querySelector('[name="workday_end"]')?.value || '17:00';
   const selected = [...workScheduleForm.querySelectorAll('input[name="working_days[]"]:checked')].map((input) => input.value);
   if (previewText) previewText.textContent = `${selected.map((day) => labels.get(day)).join(', ') || 'Belum memilih hari'} · ${start}–${end}`;
   if (previewDays) previewDays.innerHTML = [...workScheduleForm.querySelectorAll('input[name="working_days[]"]')].map((input) => `<span class="grid size-7 place-items-center rounded-md text-[10px] ${input.checked ? 'bg-blue-600 text-white' : 'bg-white text-zinc-400'}">${labels.get(input.value)}</span>`).join('');
 };
 workScheduleForm.addEventListener('input', updateWorkPreview);
 workScheduleForm.addEventListener('change', updateWorkPreview);
 updateWorkPreview();
}

const calendarRoot = document.querySelector('[data-calendar-root]');
if (calendarRoot) {
 const feedback = document.createElement('p');
 feedback.className = 'sr-only';
 feedback.setAttribute('aria-live', 'polite');
 calendarRoot.prepend(feedback);
 let calendarTask = null;
 let calendarEvent = null;
 const setCalendarFeedback = (message, failed = false) => {
   feedback.className = `rounded-xl border px-4 py-3 text-sm ${failed ? 'border-red-200 bg-red-50 text-red-700' : 'border-blue-200 bg-blue-50 text-blue-700'}`;
   feedback.textContent = message;
   feedback.classList.remove('sr-only');
 };
 const wallMinutes = (value) => {
   const [date, time] = value.split('T');
   const [year, month, day] = date.split('-').map(Number);
   const [hour, minute] = time.split(':').map(Number);
   return Date.UTC(year, month - 1, day, hour, minute) / 60000;
 };
 const wallDateTime = (minutes) => {
   const date = new Date(minutes * 60000);
   return `${date.getUTCFullYear()}-${String(date.getUTCMonth() + 1).padStart(2, '0')}-${String(date.getUTCDate()).padStart(2, '0')}T${String(date.getUTCHours()).padStart(2, '0')}:${String(date.getUTCMinutes()).padStart(2, '0')}`;
 };
 const saveEventTime = async (eventCard, startsAt, endsAt) => {
   const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
   if (!csrf || wallMinutes(endsAt) <= wallMinutes(startsAt)) throw new Error('Waktu selesai harus setelah waktu mulai.');
   const recurring = eventCard.dataset.calendarEventRecurring === 'true';
   const payload = { starts_at: startsAt, ends_at: endsAt, timezone: eventCard.dataset.calendarEventTimezone };
   const url = recurring ? eventCard.dataset.calendarEventRescheduleUrl : eventCard.dataset.calendarEventUpdateUrl;
   const body = recurring
     ? { ...payload, original_start: eventCard.dataset.calendarEventOriginalStart }
     : { ...payload, title: eventCard.dataset.calendarEventTitle, description: eventCard.dataset.calendarEventDescription || null };
   const response = await fetch(url, {
     method: 'PATCH', credentials: 'same-origin',
     headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/json' },
     body: JSON.stringify(body),
   });
   const result = await response.json().catch(() => null);
   if (!response.ok || !result?.event) throw new Error(result?.message || `HTTP ${response.status}`);
   window.location.reload();
 };
 calendarRoot.querySelectorAll('[data-calendar-event-id]').forEach((eventCard) => {
   eventCard.addEventListener('dragstart', (event) => {
     if (event.target.closest('button, a, input, select, textarea, form, summary')) { event.preventDefault(); return; }
     calendarEvent = eventCard;
     event.dataTransfer.effectAllowed = 'move';
     event.dataTransfer.setData('text/plain', eventCard.dataset.calendarEventId || '');
     eventCard.classList.add('opacity-50');
   });
   eventCard.addEventListener('dragend', () => {
     eventCard.classList.remove('opacity-50');
     calendarEvent = null;
     calendarRoot.querySelectorAll('[data-calendar-drop-date]').forEach((zone) => zone.classList.remove('is-over'));
   });
 });
 calendarRoot.querySelectorAll('[data-calendar-duration-adjust]').forEach((button) => {
   button.addEventListener('click', async () => {
     const eventCard = button.closest('[data-calendar-event-id]');
     if (!eventCard) return;
     const startsAt = eventCard.dataset.calendarEventStart;
     const nextEnd = wallDateTime(wallMinutes(eventCard.dataset.calendarEventEnd) + Number(button.dataset.calendarDurationAdjust));
     if (wallMinutes(nextEnd) - wallMinutes(startsAt) < 15) { setCalendarFeedback('Durasi agenda minimum 15 menit.', true); return; }
     try {
       setCalendarFeedback('Menyimpan durasi agenda…');
       await saveEventTime(eventCard, startsAt, nextEnd);
     } catch (error) {
       setCalendarFeedback(`Durasi gagal diperbarui. ${error?.message || 'Coba lagi.'}`, true);
     }
   });
 });
 calendarRoot.querySelectorAll('[data-calendar-task-id]').forEach((task) => {
   task.addEventListener('dragstart', (event) => {
     calendarTask = task;
     event.dataTransfer.effectAllowed = 'copy';
     event.dataTransfer.setData('text/plain', task.dataset.calendarTaskId || '');
     task.classList.add('opacity-50');
   });
   task.addEventListener('dragend', () => {
     task.classList.remove('opacity-50');
     calendarTask = null;
     calendarRoot.querySelectorAll('[data-calendar-drop-date]').forEach((zone) => zone.classList.remove('is-over'));
   });
 });
 calendarRoot.querySelectorAll('[data-calendar-drop-date]').forEach((zone) => {
   zone.addEventListener('dragover', (event) => {
     if (!calendarTask && !calendarEvent) return;
     event.preventDefault();
     event.dataTransfer.dropEffect = calendarEvent ? 'move' : 'copy';
     zone.classList.add('is-over');
   });
   zone.addEventListener('dragleave', (event) => {
     if (!zone.contains(event.relatedTarget)) zone.classList.remove('is-over');
   });
   zone.addEventListener('drop', async (event) => {
     if (!calendarTask && !calendarEvent) return;
     event.preventDefault();
     event.stopPropagation();
     zone.classList.remove('is-over');
     if (calendarEvent) {
       const eventCard = calendarEvent;
       const date = zone.dataset.calendarDropDate;
       const hour = Number(zone.dataset.calendarDropHour || 9);
       const startsAt = `${date}T${String(hour).padStart(2, '0')}:00`;
       const duration = wallMinutes(eventCard.dataset.calendarEventEnd) - wallMinutes(eventCard.dataset.calendarEventStart);
       const endsAt = wallDateTime(wallMinutes(startsAt) + Math.max(15, duration));
       calendarEvent = null;
       try {
         setCalendarFeedback(`Memindahkan “${eventCard.dataset.calendarEventTitle}”…`);
         await saveEventTime(eventCard, startsAt, endsAt);
       } catch (error) {
         console.error('DevFlow calendar move failed:', error);
         setCalendarFeedback(`Agenda gagal dipindahkan. ${error?.message || 'Coba lagi.'}`, true);
       }
       return;
     }
     const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
     const taskId = calendarTask.dataset.calendarTaskId;
     const title = calendarTask.dataset.calendarTaskTitle;
     const date = zone.dataset.calendarDropDate;
     let hour = Number(zone.dataset.calendarDropHour || 9);
     if (!csrf || !taskId || !date || !title) {
       setCalendarFeedback('Jadwal gagal disimpan. Muat ulang halaman lalu coba lagi.', true);
       return;
     }
     const startsAt = `${date}T${String(hour).padStart(2, '0')}:00`;
     const [year, month, day] = date.split('-').map(Number);
     const endDate = new Date(Date.UTC(year, month - 1, day, hour + 1));
     const endDateString = `${endDate.getUTCFullYear()}-${String(endDate.getUTCMonth() + 1).padStart(2, '0')}-${String(endDate.getUTCDate()).padStart(2, '0')}`;
     const endsAt = `${endDateString}T${String(endDate.getUTCHours()).padStart(2, '0')}:${String(endDate.getUTCMinutes()).padStart(2, '0')}`;
     setCalendarFeedback(`Menjadwalkan “${title}”…`);
     try {
       const response = await fetch(calendarRoot.dataset.calendarStoreUrl, {
         method: 'POST', credentials: 'same-origin',
         headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/json' },
         body: JSON.stringify({ title, task_id: Number(taskId), starts_at: startsAt, ends_at: endsAt, timezone: calendarRoot.dataset.calendarTimezone }),
       });
       const payload = await response.json().catch(() => null);
       if (!response.ok || !payload?.event) throw new Error(payload?.message || `HTTP ${response.status}`);
       setCalendarFeedback(`“${title}” dijadwalkan.`);
       window.setTimeout(() => window.location.reload(), 350);
     } catch (error) {
       console.error('DevFlow calendar scheduling failed:', error);
       setCalendarFeedback(`Tugas gagal dijadwalkan. ${error?.message || 'Coba lagi.'}`, true);
     }
   });
 });
}

// Project menus close predictably without affecting document/version details.
document.querySelectorAll('[data-project-menu], [data-task-menu]').forEach((menu) => {
  const panel = menu.querySelector('[data-project-menu-panel], [data-task-menu-panel]');
  const fitPanel = () => {
    if (!menu.open || !panel) return;
    const bounds = menu.getBoundingClientRect();
    const below = window.innerHeight - bounds.bottom - 24;
    const above = bounds.top - 80;
    const openAbove = below < 220 && above > below;
    panel.style.top = openAbove ? 'auto' : '100%';
    panel.style.bottom = openAbove ? 'calc(100% + 8px)' : 'auto';
    panel.style.maxHeight = `${Math.max(80, openAbove ? above : below)}px`;
  };
  menu.addEventListener('toggle', fitPanel);
  window.addEventListener('resize', fitPanel);
  document.addEventListener('click', (event) => {
    if (menu.open && !menu.contains(event.target) && !event.target.closest('dialog')) menu.open = false;
  });
  menu.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && menu.open) {
      menu.open = false;
      menu.querySelector('summary')?.focus();
      event.stopPropagation();
    }
  });
});

// Keep the native submission intact while preventing accidental repeat clicks.
document.querySelectorAll('[data-busy-form]').forEach((form) => {
  let submitting = false;
  form.addEventListener('submit', (event) => {
    if (event.defaultPrevented) return;
    if (submitting) {
      event.preventDefault();
      return;
    }
    submitting = true;
    form.setAttribute('aria-busy', 'true');
    const button = event.submitter || form.querySelector('[data-busy-label]');
    if (button) {
      button.dataset.idleLabel = button.textContent;
      button.textContent = button.dataset.busyLabel || 'Menyimpan…';
      button.setAttribute('aria-disabled', 'true');
      button.classList.add('opacity-60', 'cursor-wait');
    }
  });
  window.addEventListener('pageshow', () => {
    submitting = false;
    form.removeAttribute('aria-busy');
    form.querySelectorAll('[data-idle-label]').forEach((button) => {
      button.textContent = button.dataset.idleLabel;
      button.removeAttribute('aria-disabled');
      button.classList.remove('opacity-60', 'cursor-wait');
    });
  });
});

// Unselected drafts stay available to review but never block a valid submission.
document.querySelectorAll('[data-task-draft]').forEach((draft) => {
  const choice = draft.querySelector('input[type="checkbox"]');
  const fields = draft.querySelector('[data-draft-fields]');
  if (!choice || !fields) return;
  const update = () => { fields.disabled = !choice.checked; };
  choice.addEventListener('change', update);
  update();
});

const developmentContextBuilder = document.querySelector('[data-context-builder]');
if (developmentContextBuilder) {
  const sectionsElement = developmentContextBuilder.querySelector('[data-context-sections]');
  const preview = developmentContextBuilder.querySelector('[data-context-preview]');
  const format = developmentContextBuilder.querySelector('[data-context-format]');
  const status = developmentContextBuilder.querySelector('[data-context-status]');
  const copyButton = developmentContextBuilder.querySelector('[data-context-copy]');
  let contextSections = [];
  try {
    contextSections = JSON.parse(sectionsElement?.textContent || '[]');
  } catch (error) {
    if (status) status.textContent = 'Preview konteks gagal dimuat. Muat ulang halaman untuk mencoba kembali.';
    console.error('DevFlow development context could not be parsed:', error);
  }
  const selectedSections = () => {
    const selectedKeys = new Set([...developmentContextBuilder.querySelectorAll('[data-context-section]:checked')].map((input) => input.value));
    return contextSections.filter((section) => selectedKeys.has(section.key));
  };
  const renderPreview = () => {
    if (!preview) return;
    const selected = selectedSections();
    const content = selected.map((section) => `## ${section.label} · ${section.source}\n${section.content}`).join('\n\n');
    const taskContent = contextSections.find((section) => section.key === 'task')?.content || 'Tugas tidak tersedia.';
    const introduction = {
      developer: `Konteks tugas: ${taskContent}`,
      assistant: 'Gunakan hanya konteks di bawah. Jangan mengklaim telah melihat file lokal, staged changes, atau keadaan repository yang tidak disediakan. Jangan memperluas scope; tanyakan bila bukti tidak cukup.',
      team: 'Konteks untuk menyelaraskan pekerjaan dan membahas langkah berikutnya bersama tim.',
    }[format?.value || 'developer'];
    preview.value = `${introduction}\n\n${content || 'Pilih setidaknya satu sumber konteks.'}`;
    if (copyButton) copyButton.disabled = selected.length === 0;
    if (status && selected.length === 0) status.textContent = 'Pilih setidaknya satu sumber untuk membuat preview.';
    else if (status) status.textContent = `${selected.length} sumber dipilih. Periksa isi sebelum menyalin.`;
  };
  developmentContextBuilder.querySelectorAll('[data-context-section]').forEach((input) => input.addEventListener('change', renderPreview));
  format?.addEventListener('change', renderPreview);
  copyButton?.addEventListener('click', async () => {
    if (!preview || !status || copyButton.disabled) return;
    try {
      await navigator.clipboard.writeText(preview.value);
      status.textContent = 'Konteks disalin ke clipboard.';
    } catch (error) {
      preview.focus();
      preview.select();
      status.textContent = 'Clipboard tidak tersedia. Preview dipilih agar dapat disalin manual.';
    }
  });
  renderPreview();
}

document.querySelectorAll('[data-clipboard-copy]').forEach((button) => {
  button.addEventListener('click', async () => {
    const target = document.getElementById(button.dataset.copyTarget || '');
    const status = document.getElementById(button.dataset.copyStatus || '');
    if (!target || !status) return;

    const text = target.textContent?.trim() || '';
    if (!text) {
      status.textContent = 'Tidak ada teks untuk disalin.';
      return;
    }

    try {
      await navigator.clipboard.writeText(text);
      status.textContent = button.dataset.copyTarget === 'commit-hook-token' ? 'Token disalin.' : 'Perintah pemasangan disalin.';
    } catch (error) {
      const selection = window.getSelection();
      const range = document.createRange();
      range.selectNodeContents(target);
      selection?.removeAllRanges();
      selection?.addRange(range);
      status.textContent = 'Clipboard tidak tersedia. Teks sudah dipilih; salin dengan Cmd+C atau Ctrl+C.';
    }
  });
});
