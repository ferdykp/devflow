// Progressive enhancement: native forms still work without JavaScript.
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
const confirmDialog = document.createElement('dialog');
confirmDialog.className = 'w-[min(92vw,420px)] rounded-2xl border border-zinc-200 bg-white p-6 shadow-xl backdrop:bg-black/40';
confirmDialog.setAttribute('aria-label', 'Konfirmasi tindakan');
confirmDialog.innerHTML = '<h2 class="text-lg font-semibold">Konfirmasi tindakan</h2><p data-dialog-message class="mt-2 text-sm text-zinc-600"></p><div class="mt-6 flex justify-end gap-2"><button type="button" data-dialog-cancel class="rounded-xl border border-zinc-200 px-4 py-2 text-sm">Batal</button><button type="button" data-dialog-confirm class="rounded-xl bg-red-700 px-4 py-2 text-sm font-semibold text-white">Lanjutkan</button></div>';
if(document.querySelector('form[data-confirm]')){
 document.body.append(confirmDialog);
 let pendingForm = null;
 document.querySelectorAll('form[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{event.preventDefault(); pendingForm=form; confirmDialog.querySelector('[data-dialog-message]').textContent=form.dataset.confirm; confirmDialog.showModal(); confirmDialog.querySelector('[data-dialog-cancel]').focus();}));
 confirmDialog.querySelector('[data-dialog-cancel]').addEventListener('click',()=>confirmDialog.close());
 confirmDialog.addEventListener('close',()=>{if(pendingForm){const submitter=pendingForm.querySelector('button[type=submit]');submitter?.focus();pendingForm=null;}});
 confirmDialog.querySelector('[data-dialog-confirm]').addEventListener('click',()=>{const form=pendingForm;pendingForm=null;confirmDialog.close();form?.submit();});
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
const dfPalette = document.getElementById('df-command');
const dfSearch = document.querySelector('[data-command-input]');
const dfItems = [...document.querySelectorAll('[data-command-item]')];
let dfActive = 0;
function dfVisibleItems(){return dfItems.filter(item=>!item.hidden)}
function dfHighlight(){dfVisibleItems().forEach((item,index)=>item.toggleAttribute('data-active',index===dfActive));}
function dfOpen(){if(!dfPalette || dfPalette.open)return;dfPalette.showModal();dfSearch.value='';dfItems.forEach(item=>item.hidden=false);document.querySelector('[data-command-empty]').hidden=true;dfActive=0;dfHighlight();dfSearch.focus()}
document.querySelectorAll('[data-command-open]').forEach(button=>button.addEventListener('click',dfOpen));
document.addEventListener('keydown',event=>{if((event.ctrlKey||event.metaKey)&&event.key.toLowerCase()==='k'){event.preventDefault();dfPalette?.open?dfPalette.close():dfOpen()}});
dfSearch?.addEventListener('input',()=>{const term=dfSearch.value.trim().toLowerCase();dfItems.forEach(item=>item.hidden=!item.textContent.toLowerCase().includes(term));dfActive=0;dfHighlight();document.querySelector('[data-command-empty]').hidden=dfVisibleItems().length>0});
dfSearch?.addEventListener('keydown',event=>{const visible=dfVisibleItems();if(event.key==='ArrowDown'){event.preventDefault();dfActive=Math.min(dfActive+1,visible.length-1);dfHighlight()}else if(event.key==='ArrowUp'){event.preventDefault();dfActive=Math.max(0,dfActive-1);dfHighlight()}else if(event.key==='Enter'&&visible[dfActive]){event.preventDefault();visible[dfActive].click()}});
dfPalette?.addEventListener('click',event=>{if(event.target===dfPalette)dfPalette.close()});
const dfRail=document.querySelector('[data-sidebar]');const dfScrim=document.querySelector('[data-sidebar-overlay]');
const dfRailOpen=document.querySelector('[data-sidebar-open]');
const dfRailMedia = window.matchMedia('(max-width: 850px)');
const dfStage = document.querySelector('.df-stage');
function dfSyncRail(open) {
 if (!dfRail) return;
 const mobileOpen = dfRailMedia.matches && open;
 dfRail.inert = dfRailMedia.matches && !open;
 if (dfStage) dfStage.inert = mobileOpen;
 document.body.style.overflow = mobileOpen ? 'hidden' : '';
 dfRail.toggleAttribute('data-open', mobileOpen);
 if (dfScrim) dfScrim.hidden = !mobileOpen;
 dfRailOpen?.setAttribute('aria-expanded', String(mobileOpen));
}
function dfSetRail(open) {
 dfSyncRail(open);
 if (open) document.querySelector('[data-sidebar-close]')?.focus();
 else dfRailOpen?.focus();
}
dfSyncRail(false);
dfRailMedia.addEventListener('change', () => dfSyncRail(false));
dfRailOpen?.addEventListener('click',()=>dfSetRail(true));document.querySelector('[data-sidebar-close]')?.addEventListener('click',()=>dfSetRail(false));dfScrim?.addEventListener('click',()=>dfSetRail(false));
document.addEventListener('keydown',event=>{if(event.key==='Escape'&&dfRail?.hasAttribute('data-open'))dfSetRail(false)});
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
     if (!status || select?.value === status) return;
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

// Project menus close predictably without affecting document/version details.
document.querySelectorAll('[data-project-menu]').forEach((menu) => {
  const panel = menu.querySelector('[data-project-menu-panel]');
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
    if (menu.open && !menu.contains(event.target)) menu.open = false;
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
