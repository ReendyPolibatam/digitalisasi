# Menggambar ulang 13 activity diagram dengan gaya swimlane bergaris tepi (siap dimasukkan ke Word).
# Jalankan: powershell -ExecutionPolicy Bypass -File render-diagrams.ps1
$code = @'
using System;
using System.Collections.Generic;
using System.Drawing;
using System.Drawing.Drawing2D;
using System.Drawing.Imaging;
using System.Drawing.Text;
using System.Globalization;

public class N { public string Id, Kind, Text; public float X, Y, W, H; }
public class E { public string From, FS, To, TS, Label; public float FOff, TOff; public List<PointF> Pts = new List<PointF>(); }

public class D {
    public string File, Title; public string[] Lanes; public float[] LaneW = new float[] { 300, 560 };
    public Dictionary<string, N> Nodes = new Dictionary<string, N>();
    public List<E> Edges = new List<E>();
    public D(string file, string title, string l1, string l2) { File = file; Title = title; Lanes = new string[] { l1, l2 }; }
    N Add(string id, string kind, float x, float y, float w, float h, string t) {
        N n = new N(); n.Id = id; n.Kind = kind; n.X = x; n.Y = y; n.W = w; n.H = h; n.Text = t; Nodes[id] = n; return n;
    }
    public D S(string id, float x, float y) { Add(id, "start", x, y, 28, 28, null); return this; }
    public D End(string id, float x, float y) { Add(id, "end", x, y, 32, 32, null); return this; }
    public D A(string id, float x, float y, string t) { Add(id, "act", x, y, 170, t.Split('\n').Length >= 3 ? 76 : 56, t); return this; }
    public D Q(string id, float x, float y, string t) { Add(id, "dec", x, y, 130, 66, t); return this; }
    // from/to: "id:side" atau "id:side:offset"; pts: titik belok opsional (x1,y1,x2,y2,...)
    public D L(string from, string to, string label, params float[] pts) {
        E e = new E(); string[] f = from.Split(':'); string[] t = to.Split(':');
        e.From = f[0]; e.FS = f[1]; e.FOff = f.Length > 2 ? float.Parse(f[2], CultureInfo.InvariantCulture) : 0;
        e.To = t[0]; e.TS = t[1]; e.TOff = t.Length > 2 ? float.Parse(t[2], CultureInfo.InvariantCulture) : 0;
        e.Label = label;
        for (int i = 0; i + 1 < pts.Length; i += 2) e.Pts.Add(new PointF(pts[i], pts[i + 1]));
        Edges.Add(e); return this;
    }
}

public static class Renderer {
    const float TitleH = 44, HeadH = 30, Margin = 10, Scale = 2.5f;

    static PointF Anchor(N n, string side, float off) {
        switch (side) {
            case "t": return new PointF(n.X + off, n.Y - n.H / 2);
            case "b": return new PointF(n.X + off, n.Y + n.H / 2);
            case "l": return new PointF(n.X - n.W / 2, n.Y + off);
            default:  return new PointF(n.X + n.W / 2, n.Y + off);
        }
    }

    static GraphicsPath Round(RectangleF r, float rad) {
        GraphicsPath p = new GraphicsPath(); float d = rad * 2;
        p.AddArc(r.X, r.Y, d, d, 180, 90);
        p.AddArc(r.Right - d, r.Y, d, d, 270, 90);
        p.AddArc(r.Right - d, r.Bottom - d, d, d, 0, 90);
        p.AddArc(r.X, r.Bottom - d, d, d, 90, 90);
        p.CloseFigure(); return p;
    }

    public static void Render(D d, string outDir) {
        float w = 0; foreach (float lw in d.LaneW) w += lw;
        float maxY = 0; foreach (N n in d.Nodes.Values) maxY = Math.Max(maxY, n.Y + n.H / 2);
        float bodyTop = TitleH + HeadH, h = bodyTop + maxY + 40;

        Bitmap bmp = new Bitmap((int)((w + 2 * Margin) * Scale), (int)((h + 2 * Margin) * Scale));
        bmp.SetResolution(300, 300);
        Graphics g = Graphics.FromImage(bmp);
        g.SmoothingMode = SmoothingMode.AntiAlias;
        g.TextRenderingHint = TextRenderingHint.AntiAliasGridFit;
        g.Clear(Color.White);
        g.ScaleTransform(Scale, Scale);
        g.TranslateTransform(Margin, Margin);

        Font fTitle = new Font("Arial", 15, FontStyle.Bold, GraphicsUnit.Pixel);
        Font fHead = new Font("Arial", 12.5f, FontStyle.Bold, GraphicsUnit.Pixel);
        Font fNode = new Font("Arial", 12, FontStyle.Regular, GraphicsUnit.Pixel);
        Font fDec = new Font("Arial", 11, FontStyle.Regular, GraphicsUnit.Pixel);
        Font fLbl = new Font("Arial", 11, FontStyle.Regular, GraphicsUnit.Pixel);
        StringFormat center = new StringFormat(); center.Alignment = StringAlignment.Center; center.LineAlignment = StringAlignment.Center;
        Pen thin = new Pen(Color.Black, 1f), outer = new Pen(Color.Black, 1.8f), nodePen = new Pen(Color.Black, 1.3f);
        Brush black = Brushes.Black, lblBrush = new SolidBrush(Color.FromArgb(40, 40, 40));

        // Tabel: judul, header lane, garis lane
        g.FillRectangle(new SolidBrush(Color.FromArgb(242, 242, 242)), 0, TitleH, w, HeadH);
        g.DrawString(d.Title, fTitle, black, new RectangleF(0, 0, w, TitleH), center);
        float x = 0;
        for (int i = 0; i < d.Lanes.Length; i++) {
            g.DrawString(d.Lanes[i], fHead, black, new RectangleF(x, TitleH, d.LaneW[i], HeadH), center);
            x += d.LaneW[i];
            if (i < d.Lanes.Length - 1) g.DrawLine(thin, x, TitleH, x, h);
        }
        g.DrawLine(thin, 0, TitleH, w, TitleH);
        g.DrawLine(thin, 0, bodyTop, w, bodyTop);
        g.DrawRectangle(outer, 0, 0, w, h);

        g.TranslateTransform(0, bodyTop);

        // Panah
        Pen edgePen = new Pen(Color.Black, 1.2f);
        edgePen.CustomEndCap = new AdjustableArrowCap(4.5f, 6f, true);
        foreach (E e in d.Edges) {
            N a = d.Nodes[e.From], b = d.Nodes[e.To];
            PointF s = Anchor(a, e.FS, e.FOff), t = Anchor(b, e.TS, e.TOff);
            List<PointF> pts = new List<PointF>(); pts.Add(s);
            if (e.Pts.Count > 0) pts.AddRange(e.Pts);
            else if (Math.Abs(s.X - t.X) > 0.5f && Math.Abs(s.Y - t.Y) > 0.5f)
                pts.Add(e.FS == "t" || e.FS == "b" ? new PointF(s.X, t.Y) : new PointF(t.X, s.Y));
            pts.Add(t);
            g.DrawLines(edgePen, pts.ToArray());
            if (e.Label != null) {
                PointF p0 = pts[0], p1 = pts[1];
                if (Math.Abs(p0.Y - p1.Y) < 0.5f) {
                    SizeF sz = g.MeasureString(e.Label, fLbl);
                    g.DrawString(e.Label, fLbl, lblBrush, (p0.X + p1.X) / 2 - sz.Width / 2, p0.Y - sz.Height - 2);
                } else {
                    float ly = p1.Y > p0.Y ? p0.Y + 6 : p0.Y - 20;
                    g.DrawString(e.Label, fLbl, lblBrush, p0.X + 6, ly);
                }
            }
        }

        // Node
        foreach (N n in d.Nodes.Values) {
            RectangleF r = new RectangleF(n.X - n.W / 2, n.Y - n.H / 2, n.W, n.H);
            if (n.Kind == "start") g.FillEllipse(black, r);
            else if (n.Kind == "end") {
                g.FillEllipse(Brushes.White, r); g.DrawEllipse(new Pen(Color.Black, 1.6f), r);
                g.FillEllipse(black, n.X - 10, n.Y - 10, 20, 20);
            } else if (n.Kind == "act") {
                GraphicsPath p = Round(r, 10);
                g.FillPath(Brushes.White, p); g.DrawPath(nodePen, p);
                g.DrawString(n.Text, fNode, black, r, center);
            } else {
                PointF[] dm = { new PointF(n.X, r.Top), new PointF(r.Right, n.Y), new PointF(n.X, r.Bottom), new PointF(r.Left, n.Y) };
                g.FillPolygon(Brushes.White, dm); g.DrawPolygon(nodePen, dm);
                g.DrawString(n.Text, fDec, black, r, center);
            }
        }

        g.Dispose();
        bmp.Save(System.IO.Path.Combine(outDir, d.File + ".png"), ImageFormat.Png);
        bmp.Dispose();
    }

    public static void RenderAll(string outDir) {
        List<D> all = new List<D>();
        const float L = 150, C = 470, R = 730; // pusat kolom: lane kiri, lane kanan (tengah), lane kanan (pinggir)

        all.Add(new D("UC-01_login", "Activity Diagram UC-01: Melakukan Login", "Admin / Staff", "Sistem")
            .S("s", L, 40).A("a1", L, 115, "Buka halaman\nlogin").A("a2", L, 215, "Masukkan email\ndan password")
            .Q("d1", C, 215, "Data lengkap?").A("e1", R, 215, "Tampilkan pesan\nkesalahan")
            .Q("d2", C, 325, "Data valid?").A("a3", C, 430, "Tampilkan dashboard\nsesuai hak akses").End("x", C, 520)
            .L("s:b", "a1:t", null).L("a1:b", "a2:t", null).L("a2:r", "d1:l", null)
            .L("d1:r", "e1:l", "Tidak").L("e1:t", "a2:t:40", null, R, 165, L + 40, 165)
            .L("d1:b", "d2:t", "Ya").L("d2:r", "e1:b", "Tidak").L("d2:b", "a3:t", "Ya").L("a3:b", "x:t", null));

        all.Add(new D("UC-02_logout", "Activity Diagram UC-02: Melakukan Logout", "Admin / Staff", "Sistem")
            .S("s", L, 40).A("a1", L, 120, "Pilih menu\nlogout").Q("d1", C, 120, "Sesi aktif?")
            .A("r1", R, 120, "Arahkan langsung\nke halaman login").A("a2", C, 230, "Akhiri sesi\npengguna")
            .A("a3", C, 330, "Tampilkan\nhalaman login").End("x", C, 420)
            .L("s:b", "a1:t", null).L("a1:r", "d1:l", null).L("d1:r", "r1:l", "Timeout").L("d1:b", "a2:t", "Ya")
            .L("a2:b", "a3:t", null).L("r1:b", "a3:r", null).L("a3:b", "x:t", null));

        all.Add(new D("UC-03_kelola-pengguna", "Activity Diagram UC-03: Mengelola Data Pengguna", "Admin", "Sistem")
            .S("s", L, 40).A("a1", L, 120, "Buka menu\nmanajemen pengguna").A("a2", C, 120, "Tampilkan daftar\npengguna")
            .Q("d1", L, 230, "Pilih aksi").A("a3", L, 350, "Isi atau perbarui\ndata pengguna")
            .Q("d2", C, 350, "Data valid?").A("e1", R, 350, "Tampilkan pesan\nkesalahan")
            .A("a4", C, 460, "Validasi dan\nsimpan data").A("a5", C, 560, "Tampilkan daftar\npengguna terbaru").End("x", L, 650)
            .L("s:b", "a1:t", null).L("a1:r", "a2:l", null).L("a2:b", "d1:r", null)
            .L("d1:b", "a3:t", "Tambah/ubah/hapus").L("d1:l", "x:l", "Batal", 30, 230, 30, 650)
            .L("a3:r", "d2:l", null).L("d2:r", "e1:l", "Tidak").L("e1:t", "a3:t:40", null, R, 300, L + 40, 300)
            .L("d2:b", "a4:t", "Ya").L("a4:b", "a5:t", null).L("a5:l", "x:t", null));

        all.Add(new D("UC-04_unggah-dokumen", "Activity Diagram UC-04: Mengunggah Dokumen", "Staff", "Sistem")
            .S("s", L, 40).A("a1", L, 120, "Buka menu upload").A("a2", L, 230, "Pilih file dokumen\n(gambar atau PDF)")
            .Q("d1", C, 230, "Format valid?").A("e1", R, 230, "Tampilkan pesan\nkesalahan")
            .A("a3", C, 340, "Simpan dokumen").Q("d2", C, 450, "Berhasil?")
            .A("a4", C, 560, "Jalankan OCR dan\nklasifikasi otomatis").A("a5", C, 660, "Tampilkan notifikasi\ndokumen diproses")
            .End("x", L, 750)
            .L("s:b", "a1:t", null).L("a1:b", "a2:t", null).L("a2:r", "d1:l", null).L("d1:r", "e1:l", "Tidak")
            .L("e1:t", "a2:t:40", null, R, 180, L + 40, 180).L("d1:b", "a3:t", "Ya").L("a3:b", "d2:t", null)
            .L("d2:r", "e1:b", "Tidak").L("d2:b", "a4:t", "Ya").L("a4:b", "a5:t", null).L("a5:l", "x:t", null));

        all.Add(new D("UC-05_ekstraksi-ocr", "Activity Diagram UC-05: Ekstraksi Teks (OCR)", "Staff", "Sistem")
            .S("s", C, 40).A("a1", C, 120, "Baca dokumen\nyang diunggah").A("a2", C, 220, "Proses OCR\npada dokumen")
            .Q("d1", C, 330, "OCR berhasil?").A("f1", L, 330, "Terima notifikasi\nOCR gagal").A("f2", L, 440, "Unggah ulang\ndokumen")
            .End("x1", L, 530).A("a3", C, 440, "Simpan teks hasil\nekstraksi").A("a4", C, 540, "Lanjut ke klasifikasi\ndokumen (UC-06)")
            .End("x2", C, 630)
            .L("s:b", "a1:t", null).L("a1:b", "a2:t", null).L("a2:b", "d1:t", null).L("d1:l", "f1:r", "Tidak")
            .L("f1:b", "f2:t", null).L("f2:b", "x1:t", null).L("d1:b", "a3:t", "Ya").L("a3:b", "a4:t", null).L("a4:b", "x2:t", null));

        all.Add(new D("UC-06_klasifikasi-dokumen", "Activity Diagram UC-06: Klasifikasi Dokumen", "Staff", "Sistem")
            .S("s", C, 40).A("a1", C, 120, "Baca teks\nhasil OCR").A("a2", C, 220, "Tentukan jenis\ndokumen")
            .Q("d1", C, 330, "Dikenali?").A("r1", R, 330, "Masukkan ke\nkategori umum").A("a3", C, 440, "Simpan hasil\nklasifikasi")
            .A("a4", C, 540, "Siapkan data untuk\ndiperiksa Staff").A("s1", L, 540, "Terima data untuk\ndiperiksa (UC-07)").End("x", L, 630)
            .L("s:b", "a1:t", null).L("a1:b", "a2:t", null).L("a2:b", "d1:t", null).L("d1:r", "r1:l", "Tidak")
            .L("r1:b", "a3:r", null).L("d1:b", "a3:t", "Ya").L("a3:b", "a4:t", null).L("a4:l", "s1:r", null).L("s1:b", "x:t", null));

        all.Add(new D("UC-07_review-edit-ocr", "Activity Diagram UC-07: Review dan Edit Hasil OCR", "Staff", "Sistem")
            .S("s", L, 40).A("a1", L, 120, "Buka dokumen dan\nhasil OCR").A("a2", L, 220, "Periksa kesesuaian\ndengan dokumen asli")
            .Q("d1", L, 330, "Data sesuai?").A("a3", L, 440, "Edit data yang\ntidak sesuai").A("a4", L, 540, "Simpan perubahan")
            .Q("d2", C, 540, "Data valid?").A("r1", R, 540, "Tampilkan\nperingatan").A("a5", C, 650, "Simpan data yang\ndiperbarui")
            .End("x", L, 740)
            .L("s:b", "a1:t", null).L("a1:b", "a2:t", null).L("a2:b", "d1:t", null).L("d1:b", "a3:t", "Tidak")
            .L("d1:l", "x:l", "Ya", 30, 330, 30, 740).L("a3:b", "a4:t", null).L("a4:r", "d2:l", null)
            .L("d2:r", "r1:l", "Tidak").L("r1:t", "a3:r", null).L("d2:b", "a5:t", "Ya").L("a5:l", "x:t", null));

        all.Add(new D("UC-08_mengajukan-verifikasi", "Activity Diagram UC-08: Mengajukan Verifikasi", "Staff", "Sistem")
            .S("s", L, 40).A("a1", L, 120, "Buka dokumen yang\ntelah diperiksa").A("a2", L, 230, "Pilih opsi ajukan\nverifikasi")
            .Q("d1", C, 230, "Data lengkap?").A("r1", R, 230, "Tolak pengajuan dan\nberi peringatan")
            .A("a3", C, 340, "Ubah status dokumen\nmenjadi diajukan").A("a4", C, 440, "Tampilkan konfirmasi\npengajuan").End("x", L, 530)
            .L("s:b", "a1:t", null).L("a1:b", "a2:t", null).L("a2:r", "d1:l", null).L("d1:r", "r1:l", "Tidak")
            .L("r1:t", "a2:t:40", null, R, 180, L + 40, 180).L("d1:b", "a3:t", "Ya").L("a3:b", "a4:t", null).L("a4:l", "x:t", null));

        all.Add(new D("UC-09_verifikasi-dokumen", "Activity Diagram UC-09: Verifikasi Dokumen", "Admin", "Sistem")
            .S("s", L, 40).A("a1", L, 120, "Buka daftar dokumen\nyang diajukan").A("a2", L, 220, "Periksa dokumen\nbeserta datanya")
            .Q("d1", L, 330, "Keputusan?").A("b1", C, 330, "Tetapkan status\napproved (UC-10)").A("b2", C, 430, "Simpan sebagai arsip\nfinal (UC-11)")
            .End("x1", C, 520).A("c1", L, 610, "Isi catatan\npenolakan").A("c2", C, 610, "Tetapkan status\nrejected (UC-10)")
            .A("c3", C, 710, "Kembalikan dokumen\nke Staff (UC-12)").End("x2", C, 800)
            .L("s:b", "a1:t", null).L("a1:b", "a2:t", null).L("a2:b", "d1:t", null).L("d1:r", "b1:l", "Approve")
            .L("b1:b", "b2:t", null).L("b2:b", "x1:t", null).L("d1:b", "c1:t", "Reject").L("c1:r", "c2:l", null)
            .L("c2:b", "c3:t", null).L("c3:b", "x2:t", null));

        all.Add(new D("UC-10_menetapkan-status", "Activity Diagram UC-10: Menetapkan Status Dokumen", "Admin", "Sistem")
            .S("s", C, 40).A("a1", C, 120, "Terima keputusan\ndari Admin").A("a2", C, 220, "Tetapkan status\napproved/rejected")
            .A("a3", C, 320, "Simpan status, waktu\ndan nama Admin").Q("d1", C, 430, "Tersimpan?")
            .A("e1", L, 430, "Terima pesan error,\nstatus tidak berubah").A("a4", C, 540, "Tampilkan\nkonfirmasi")
            .A("a5", L, 540, "Terima\nkonfirmasi").End("x", L, 630)
            .L("s:b", "a1:t", null).L("a1:b", "a2:t", null).L("a2:b", "a3:t", null).L("a3:b", "d1:t", null)
            .L("d1:l", "e1:r", "Tidak").L("e1:l", "x:l", null, 30, 430, 30, 630).L("d1:b", "a4:t", "Ya")
            .L("a4:l", "a5:r", null).L("a5:b", "x:t", null));

        all.Add(new D("UC-11_arsip-riwayat", "Activity Diagram UC-11: Menyimpan Arsip dan Riwayat", "Sistem", "Basis Data")
            .S("s", L, 40).Q("d1", L, 130, "Approved?").A("r1", C, 130, "Catat riwayat\npenolakan saja").End("x1", C, 220)
            .A("a1", L, 240, "Tandai dokumen\nsebagai arsip final").A("a2", L, 340, "Catat status, waktu,\ndan pengguna")
            .A("b1", C, 340, "Simpan arsip ke\nbasis data").Q("d2", C, 450, "Berhasil?").A("e1", R, 450, "Tampilkan pesan\nerror, ulangi")
            .A("b2", C, 560, "Dokumen dapat\ndicari").End("x2", C, 650)
            .L("s:b", "d1:t", null).L("d1:r", "r1:l", "Tidak").L("r1:b", "x1:t", null).L("d1:b", "a1:t", "Ya")
            .L("a1:b", "a2:t", null).L("a2:r", "b1:l", null).L("b1:b", "d2:t", null).L("d2:r", "e1:l", "Tidak")
            .L("e1:t", "b1:r", null).L("d2:b", "b2:t", "Ya").L("b2:b", "x2:t", null));

        all.Add(new D("UC-12_perbaikan-unggah-ulang", "Activity Diagram UC-12: Perbaikan dan Unggah Ulang Dokumen", "Staff", "Sistem")
            .S("s", L, 40).A("a1", L, 120, "Buka dokumen ditolak\nbeserta catatannya").A("a2", L, 220, "Perbaiki data/dokumen\nsesuai catatan")
            .Q("d1", L, 330, "Diperbaiki?").A("a3", L, 450, "Unggah ulang\ndokumen").Q("d2", C, 450, "Format valid?")
            .A("e1", R, 450, "Tampilkan pesan\nkesalahan").A("b1", C, 560, "Proses ulang dokumen\n(UC-04 s/d UC-06)")
            .A("b2", C, 660, "Simpan versi terbaru\ndan catat riwayat").A("a4", L, 660, "Ajukan kembali untuk\nverifikasi (UC-08)").End("x", L, 750)
            .L("s:b", "a1:t", null).L("a1:b", "a2:t", null).L("a2:b", "d1:t", null).L("d1:l", "x:l", "Tidak", 30, 330, 30, 750)
            .L("d1:b", "a3:t", "Ya").L("a3:r", "d2:l", null).L("d2:r", "e1:l", "Tidak").L("e1:t", "a3:t:40", null, R, 400, L + 40, 400)
            .L("d2:b", "b1:t", "Ya").L("b1:b", "b2:t", null).L("b2:l", "a4:r", null).L("a4:b", "x:t", null));

        all.Add(new D("UC-13_pencarian-detail", "Activity Diagram UC-13: Pencarian dan Detail Dokumen", "Admin / Staff", "Sistem")
            .S("s", L, 40).A("a1", L, 120, "Buka menu pencarian\ndokumen").A("a2", L, 220, "Masukkan kata kunci")
            .A("b1", C, 220, "Cari data\ndokumen").Q("d1", C, 330, "Ditemukan?").A("e1", R, 330, "Tampilkan pesan\ndokumen tidak ada")
            .A("b2", C, 440, "Tampilkan daftar\nhasil pencarian").A("a3", L, 440, "Pilih salah satu\ndokumen")
            .A("b3", C, 560, "Tampilkan detail:\nOCR, klasifikasi,\nstatus, riwayat").End("x", C, 660)
            .L("s:b", "a1:t", null).L("a1:b", "a2:t", null).L("a2:r", "b1:l", null).L("b1:b", "d1:t", null)
            .L("d1:r", "e1:l", "Tidak").L("d1:b", "b2:t", "Ya").L("b2:l", "a3:r", null).L("a3:b", "b3:l", null)
            .L("b3:b", "x:t", null).L("e1:b", "x:r", null));

        foreach (D d in all) Render(d, outDir);
    }
}
'@
Add-Type -TypeDefinition $code -ReferencedAssemblies System.Drawing
$out = Join-Path $PSScriptRoot "activity-diagrams-bergaris"
New-Item -ItemType Directory -Force $out | Out-Null
[Renderer]::RenderAll($out)
Get-ChildItem $out | Select-Object Name, Length
