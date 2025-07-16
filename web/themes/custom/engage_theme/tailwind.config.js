/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './assets/src/**/*.{js,ts,jsx,tsx,css,scss}',
    './templates/**/*.{html,twig}',
    '../../templates/**/*.twig',
    '../../../modules/custom/**/templates/**/*.twig',
    '../../../modules/custom/**/*.js',
    '../../../themes/custom/**/templates/**/*.twig',
    '../../../themes/custom/**/components/**/*.twig',
    './src/**/*.php',
  ],
  safelist: [
    'w-28', 'rounded-full', 'aspect-square', 'mt-2',
    'text-sm', 'font-bold', 'font-[Open_Sans]', 'text-[#a0a0a0]',
    'border-2', 'px-4', 'py-0.5', 'cursor-pointer', 'translateLabel',
    'flex', 'justify-center', 'my-4', 'text-[13px]', 'text-[#646262]',
    'btn', 'bg-yellow', 'hover:bg-button_hover', 'w-44', 'h-12',
    'text-xl', 'update-btn', 'button', 'js-form-submit', 'form-submit',
    'animate-fade-in-down','w-[25px]'
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ["Open Sans", "system-ui", "sans-serif"],
        nevis: ["Nevis", "sans-serif"],
        "nevis-bold": ["Nevis Bold", "sans-serif"],
      },
      colors: {
        gray83: "rgb(83 83 83)",
      },
      keyframes: {
        'fade-in-down': {
          '0%': { opacity: '0', transform: 'translateY(-20px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
      },
      animation: {
        'fade-in-down': 'fade-in-down 0.5s ease-out',
      },
    },
  },
  plugins: [],
};
