/**
 * Tipe data untuk halaman "Proposal Saya".
 *
 * Seluruh bentuk data ini berasal dari backend (App\Http\Controllers\ProposalController
 * + App\Services\ProposalService) — tidak ada data dummy di frontend.
 */

/** Status turunan proposal (lihat App\Enums\ProposalStatus). */
export type ProposalStatusKey =
    | 'draft'
    | 'ready'
    | 'sent'
    | 'waiting'
    | 'approved'
    | 'rejected';

/** Key kartu statistik. */
export type ProposalStatKey = 'sent' | 'approved' | 'waiting' | 'rejected';

/** Satu kartu statistik. */
export type ProposalStat = {
    key: ProposalStatKey;
    label: string;
    value: number;
    previous: number;
    /** Persen perubahan vs periode sebelumnya; null = tidak ada pembanding. */
    change: number | null;
};

/** Data grafik tren (dua seri). */
export type ProposalTrend = {
    labels: string[];
    sent: number[];
    approved: number[];
};

/** Periode grafik yang tersedia. */
export type ProposalPeriod = 7 | 30 | 90;

/** Jenis aksi tindak lanjut. */
export type FollowUpAction = 'follow-up' | 'revisi' | 'konfirmasi';

/** Satu item pada panel "Perlu Tindak Lanjut". */
export type FollowUpItem = {
    id: number;
    proposalId: number;
    proposalTitle: string;
    companyName: string;
    action: FollowUpAction;
    actionLabel: string;
    description: string;
    sentAt: string | null;
    lastFollowUpAt: string | null;
};

/** Payload panel tindak lanjut. */
export type FollowUpPayload = {
    total: number;
    items: FollowUpItem[];
};

/** Satu baris pada tabel proposal. */
export type ProposalRow = {
    id: number;
    nomor: string;
    title: string;
    status: ProposalStatusKey;
    statusLabel: string;
    kategori: string | null;
    eventName: string;
    eventDate: string | null;
    sponsorName: string | null;
    sponsorIndustry: string | null;
    sponsorInitials: string;
    sentAt: string | null;
    budgetMin: number;
    budgetMax: number;
    trackingId: number | null;
    canFollowUp: boolean;
    documentAvailable: boolean;
};

/** Satu entri kronologi status. */
export type ProposalTimelineEntry = {
    label: string;
    description: string;
    at: string;
};

/** Detail proposal lengkap untuk panel kanan. */
export type ProposalDetail = {
    id: number;
    /** Nomor urut proposal milik user ini (1, 2, 3, ...), bukan id database. */
    nomor: string;
    title: string;
    status: ProposalStatusKey;
    statusLabel: string;
    kategori: string | null;
    description: string;
    needType: string;
    event: {
        name: string;
        kategori: string | null;
        date: string | null;
        endDate: string | null;
        location: string;
        targetAudience: string;
        estimatedParticipant: number | null;
        thumbnail: string | null;
    };
    sponsor: {
        name: string | null;
        industry: string | null;
        initials: string;
    };
    offer: {
        budgetMin: number;
        budgetMax: number;
        amount: number | null;
        supportType: string | null;
        status: string | null;
        benefits: string[];
    };
    document: {
        fileName: string;
        path: string;
        size: number | null;
        uploadedAt: string | null;
        available: boolean;
    };
    timeline: ProposalTimelineEntry[];
    /** Laporan realisation event sponsor (kalau sudah ada). */
    report: {
        description: string;
        goalAchieved: boolean;
        submittedAt: string | null;
    } | null;
    actions: {
        hasReport: boolean;
        canFollowUp: boolean;
        trackingId: number | null;
    };
};

/** Tautan pagination (format Laravel paginator). */
export type ProposalPaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

/** Hasil pagination tabel proposal. */
export type ProposalPagination = {
    data: ProposalRow[];
    currentPage: number;
    lastPage: number;
    total: number;
    perPage: number;
    from: number | null;
    to: number | null;
    links: ProposalPaginationLink[];
};

/** Filter aktif halaman. */
export type ProposalFilters = {
    search: string;
    status: ProposalStatusKey | 'all';
    periode: ProposalPeriod;
    perPage: number;
    kategori: number | null;
};

/** Opsi kategori event (untuk form & filter). */
export type ProposalKategori = {
    id: number;
    name: string;
};

/** Jumlah proposal per tab status. */
export type ProposalStatusCounts = Record<ProposalStatusKey | 'all', number>;
