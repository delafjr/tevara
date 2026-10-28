// tailwind.config.js
module.exports = {
  content: ['./resources/**/*.blade.php', './resources/**/*.js', './resources/**/*.vue'],
  theme: {
    extend: {
      colors: {
        // Brand Colors (TEVARA Design System)
        navy: '#0B3B82',          // Warna utama brand: heading, button, navbar
        primary: '#0B3B82',
        yellow: '#FFB800',        // Warna aksen: CTA, highlight, grafis
        accent: '#FFB800',
        secondary: '#FFB800',
        white: '#FFFFFF',         // Background utama
        'light-blue': '#EAF3FF',  // Background section, card sekunder
        section: '#EAF3FF',
        'surface-soft': '#F8FAFC',

        // Typography
        'dark-text': '#1E293B',   // Warna teks utama
        'text-primary': '#1E293B',
        gray: '#64748B',          // Warna teks sekunder
        'text-secondary': '#64748B',
        'text-placeholder': '#64748B',

        // Status Colors
        success: '#16A34A',       // Selesai, berhasil, approve
        warning: '#F59E0B',       // Menunggu, perlu perhatian
        error: '#DC2626',         // Ditolak, error
        danger: '#DC2626',
      },
      spacing: {
        4: '1rem',
        5: '1.25rem',
        6: '1.5rem',
        7: '1.75rem',
        8: '2rem',
      },
      borderRadius: {
        lg: '0.75rem',
        xl: '1rem',
        '2xl': '1.25rem',
      },
      boxShadow: {
        sm: '0 1px 2px rgba(0,0,0,0.05)',
        md: '0 4px 6px rgba(0,0,0,0.1)',
      },
    },
  },
  plugins: [],
};
