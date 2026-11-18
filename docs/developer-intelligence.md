# Developer Intelligence DevFlow

**Status:** Domain kapabilitas dan proposal; implementasi Context Generator/Change Request belum disetujui.

Developer Intelligence membantu developer memahami alasan task, menyiapkan konteks yang benar, menjaga scope, dan melanjutkan pekerjaan dengan bukti. Ini bukan keharusan membuat menu baru atau menambahkan AI ke setiap alur.

## Kapabilitas yang sudah ada

| Kapabilitas | Bukti source | Status |
|---|---|---|
| Scope baseline manual | `scope_baselines`, `ScopeBaseline`, route `projects/{project}/scope-baseline` | Existing / Verified |
| Scope review/decision/backlog | `scope_reviews`, `scope_decisions`, `scope_events`; transaksi keputusan dan promosi ke backlog | Existing / Verified; classification Needs Improvement |
| AI project/scope insight | `ProjectInsights`; dipanggil eksplisit; advisory; tidak memutasi data | Existing / Verified; bukan Context Generator |
| PRD versi dan persetujuan | `project_documents`, `prd_versions`; create draft, revise creates next version, approve hashes content | Existing / Needs Improvement: immutable hanya dijaga oleh jalur aplikasi saat ini, bukan invariant database/model. |
| Import traceability | Import terkait versi PRD; task menyimpan `document_task_import_id` dan `source_index` | Existing / Verified; UX link balik perlu ditinjau. |
| Project Resume | `ProjectResumeService` menyusun open task, recent activity, notes, milestone, progress, GitHub sync, PRD presence | Existing / Verified secara source; freshness dan evidence UI perlu dinilai. |
| GitHub activity | Repository multi-link per project, webhook signature, delivery dedup, queued sync, reconcile command, sync/error time | Existing / Verified secara source; provider nyata NOT TESTED pada audit ini. |
| Commit assistant | `tools/devflow-commit.py`; staged snapshot, local preview, optional AI consent, diff bound, confirmation | Existing / Verified; standalone CLI, bukan extension. |

## Proposal: Development Context Generator

**Masalah:** developer membuka beberapa tempat untuk memahami apa yang perlu dibuat dan batas perubahan.  
**Entry point:** aksi kontekstual pada detail Task proyek, dengan jalan pintas dari Project Resume.  
**Keputusan:** Proposed; implementasi menunggu persetujuan.

### Output deterministik

1. Ringkasan untuk developer.
2. Prompt coding assistant yang dapat disalin.
3. Ringkasan konteks untuk diskusi tim.

### Sumber yang boleh disertakan

- Project dan Task yang sedang dibuka.
- Import source anchor dan versi PRD terkait jika ada.
- Acceptance criteria/description, milestone, checklist.
- Baseline/scope decision yang benar-benar terkait.
- Catatan proyek/keputusan yang dipilih pengguna.
- Aktivitas terbaru, GitHub hanya jika terhubung dan hak akses tersedia.
- Definition of Done dan explicit constraints yang tersimpan.

Jangan mengeklaim tahu file lokal, staged diff, repo checkout, atau status deploy jika integrasi tidak memberikan data tersebut. Jangan memasukkan token, secret, atau seluruh dokumen secara otomatis.

### Acceptance criteria proposal

- Menampilkan preview sebelum copy atau pengiriman ke provider.
- Pengguna dapat memilih kelompok konteks yang disertakan.
- Setiap bagian menaut ke sumber DevFlow dan menandai waktu/freshness.
- Data yang tidak ada dinyatakan “tidak tersedia”; tidak dibuat-buat.
- Salin lokal bekerja tanpa AI/provider.
- Pengiriman ke provider bersifat opt-in, provider dan data yang dikirim dijelaskan tepat sebelum tindakan.
- Context hanya berasal dari project/task yang dapat diakses pengguna.
- Pengujian memeriksa project isolation, no-secret payload, source links, dan fallback tanpa AI.

## Scope Guardian dan manual baseline

Baseline saat ini menyimpan `in_scope` dan `out_of_scope`; tujuan proyek berada pada field `projects.goal`. Constraints, assumptions, dan success criteria belum memiliki field khusus pada schema saat ini. Pengguna tidak perlu mengisi baseline untuk membuat proyek.

Classification historis yang harus dipertimbangkan: `in_scope`, `potential_out_of_scope`, `focus_misalignment`, `unplanned`, `uncertain`, `not_assessable`. Source sekarang memakai subset berbeda (`in_scope`, `out_of_scope`, `uncertain`, `not_assessable`), sehingga penyelarasan enum adalah keputusan produk dan migrasi kompatibilitas, bukan bug yang boleh diganti diam-diam.

### Aturan produk

- Tanpa baseline otoritatif: jangan tampilkan verdict pasti.
- AI selalu advisory; manusia menyimpan keputusan.
- Rekam actor, timestamp, alasan, evidence dan source anchor.
- Memindahkan ide ke backlog tidak mengubah PRD, task aktif, atau progres.
- Persetujuan rencana perubahan, approval versi PRD, dan pembuatan task adalah aksi berbeda.

## Proposal: Change Request dan versioning

Flow calon: `Ide → Scope Review → Change Request → Decision → PRD Draft Revision → Approval`. Flow ringan untuk solo developer dapat menggabungkan layar, tetapi tetap memisahkan record/aksi persetujuan.

Approved PRD harus immutable. Sebelum menambah relasi ke scope decision, tetapkan aturan bila versi sumber dihapus, direvisi, atau disupersede. Migration/backfill perlu mengikat keputusan lama ke sumber yang dapat dibuktikan; jika tidak ada bukti, simpan status legacy/unknown dan jangan mengarang versi.

## GitHub dan Project Resume

GitHub merupakan bukti remote. Ia tidak menyelesaikan task atau menyetujui perubahan. Resume harus menunjukkan data terakhir dan `last_synced_at`/error dari repository, link ke aktivitas sumber, dan langkah berikutnya yang merupakan saran. Resume tetap bermakna untuk proyek tanpa PRD, GitHub, atau task.

## Kriteria roadmap

Urutkan kerja Context Generator dan Scope Guardian setelah Release 1 memenuhi task lifecycle, authorization, import/source link, dan resume data freshness. CLI tetap optional. VS Code Extension menunggu API dan otorisasi yang stabil serta validasi kebutuhan.

## Addendum implementasi lokal — 18 November 2026

Atas permintaan implementasi terbaru, kemampuan berikut kini ada di source dan dites:

- Context Generator deterministic pada detail task; preview bisa disaring per bagian, punya tiga format, source links, secret redaction, dan tidak mengirim ke AI.
- Manual baseline meliputi scope, constraints, assumptions, success criteria, dan Definition of Done. Anchor PRD opsional.
- Scope review memakai salah satu enam klasifikasi historis, menyimpan evidence/reason/actor/time dan nullable PRD anchor. Tidak ada verdict pasti saat acuan kosong.
- Change Request workflow memiliki proposed/approved/rejected/deferred, actor dan reason, optional scope/source version, serta action terpisah untuk membuat PRD draft.
- Model PRD menjaga versi approved/superseded tetap immutable.
- Notification in-app tercipta dari komentar, scope review, dan Change Request; read/unread, mark-all-read, dismiss, dan user isolation tersedia.

Status proposal di bagian audit tetap merupakan snapshot sebelumnya. Reminder scheduler/email, AI provider, extension, dan integrasi eksternal tetap tidak aktif.
