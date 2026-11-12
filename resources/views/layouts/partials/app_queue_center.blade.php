@auth
<button type="button" id="queueCenterFab" class="d-none"
        aria-label="Queue Center"
        style="position: fixed; right: 16px; bottom: 96px; z-index: 1200; width: 54px; height: 54px; border-radius: 999px;
               display: grid; place-items: center;
               border: 1px solid var(--border-glass);
               background: rgba(255,255,255,0.07);
               color: var(--text-primary);
               box-shadow: 0 18px 40px rgba(0,0,0,0.28);
               backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);">
    <i class="fas fa-list-check" style="font-size: 18px;"></i>
    <span id="queueCenterCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill"
          style="background: linear-gradient(135deg, var(--accent-cyan), var(--accent-blue)); border: 1px solid rgba(255,255,255,0.35); color: #fff; font-weight: 900;">
        0
    </span>
    <span id="queueCenterDot" class="position-absolute top-0 start-100 translate-middle p-1 bg-danger rounded-circle d-none"
          style="margin-left: -20px; margin-top: 16px; box-shadow: 0 0 0 2px rgba(15,23,42,0.6);"></span>
</button>

<div class="modal fade" id="queueCenterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content apple-glass apple-glass-card" style="border-radius: 22px; border: 1px solid var(--border-glass); overflow: hidden;">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-glass);">
                <div>
                    <div class="small fw-800" style="letter-spacing: 0.14em; text-transform: uppercase; color: var(--text-secondary);">Queue Center</div>
                    <div class="fw-900" style="color: var(--text-primary); font-size: 1.2rem;">Status Antrian</div>
                    <div class="small apple-hint" id="queueCenterHint">Pantau job AI dan batalkan jika tidak jadi.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" :class="isDarkTheme() ? 'btn-close-white' : ''"></button>
            </div>
            <div class="modal-body">
                <div id="queueCenterEmpty" class="apple-glass-soft rounded-4 p-3">
                    <div class="fw-800" style="color: var(--text-primary);">Tidak ada job aktif.</div>
                    <div class="small apple-hint mt-1">Jika kamu start Generate/Audit/Auto-Label, job akan tampil di sini.</div>
                </div>
                <div id="queueCenterList" class="vstack gap-3"></div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--border-glass);">
                <button type="button" class="apple-glass-btn apple-btn-outline" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endauth
