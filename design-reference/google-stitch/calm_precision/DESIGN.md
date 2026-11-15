---
name: Calm Precision
colors:
  surface: '#f8f9ff'
  surface-dim: '#d1daeb'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e6eeff'
  surface-container-high: '#e0e9fa'
  surface-container-highest: '#dae3f4'
  on-surface: '#131c28'
  on-surface-variant: '#434655'
  inverse-surface: '#28313e'
  inverse-on-surface: '#eaf1ff'
  outline: '#737686'
  outline-variant: '#c3c6d7'
  surface-tint: '#0053da'
  primary: '#004bc6'
  on-primary: '#ffffff'
  primary-container: '#2463eb'
  on-primary-container: '#eeefff'
  inverse-primary: '#b4c5ff'
  secondary: '#535f70'
  on-secondary: '#ffffff'
  secondary-container: '#d7e3f7'
  on-secondary-container: '#596576'
  tertiary: '#943700'
  on-tertiary: '#ffffff'
  tertiary-container: '#bc4800'
  on-tertiary-container: '#ffede6'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dbe1ff'
  primary-fixed-dim: '#b4c5ff'
  on-primary-fixed: '#00174b'
  on-primary-fixed-variant: '#003ea7'
  secondary-fixed: '#d7e3f7'
  secondary-fixed-dim: '#bbc7da'
  on-secondary-fixed: '#101c2a'
  on-secondary-fixed-variant: '#3c4857'
  tertiary-fixed: '#ffdbcd'
  tertiary-fixed-dim: '#ffb596'
  on-tertiary-fixed: '#360f00'
  on-tertiary-fixed-variant: '#7d2d00'
  background: '#f8f9ff'
  on-background: '#131c28'
  surface-variant: '#dae3f4'
  canvas-light: '#F8F9FB'
  surface-light: '#FFFFFF'
  surface-muted: '#F1F4F8'
  border-subtle: '#E2E7EE'
  primary-hover: '#194BC0'
  focus-ring: '#93B4FF'
  success: '#16835F'
  warning: '#A96716'
  danger: '#C43B40'
  canvas-dark: '#111720'
  surface-dark: '#1B2430'
  text-dark: '#E9EDF5'
  border-dark: '#354154'
typography:
  headline-lg:
    fontFamily: Geist
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-lg-mobile:
    fontFamily: Geist
    fontSize: 26px
    fontWeight: '600'
    lineHeight: 34px
    letterSpacing: -0.015em
  headline-md:
    fontFamily: Geist
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.015em
  headline-sm:
    fontFamily: Geist
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
    letterSpacing: -0.01em
  body-lg:
    fontFamily: Geist
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-md:
    fontFamily: Geist
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  body-sm:
    fontFamily: Geist
    fontSize: 12px
    fontWeight: '400'
    lineHeight: 16px
  label-code-md:
    fontFamily: JetBrains Mono
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 20px
  label-code-sm:
    fontFamily: JetBrains Mono
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
  label-tag:
    fontFamily: Geist
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
    letterSpacing: 0.01em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1rem
  gutter-desktop: 1.5rem
  margin: 1rem
  margin-desktop: 2rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 0.75rem
  space-lg: 1rem
  space-xl: 1.5rem
  space-2xl: 2rem
---

# DevFlow UI Design System — High-Fidelity Specification

## Desired personality
Premium calm productivity, with clarity and precision of a mature developer tool. Editorial, disciplined, practical. Avoid generic AI-generated dashboards. Swiss-influenced typography and layout discipline.

## Color system
- Canvas: `#F8F9FB`; Surface: `#FFFFFF`; Subtle surface / muted: `#F1F4F8`
- Primary text: `#17202C`; Secondary text: `#677384`; Border: `#E2E7EE`
- Primary action / Brand: `#2463EB`; Primary hover: `#194BC0`; Focus ring: `#93B4FF`
- Success: `#16835F`; Warning: `#A96716`; Danger: `#C43B40`
- Dark theme (tokens): Canvas `#111720`; Surface `#1B2430`; Text `#E9EDF5`; Borders `#354154`

## Typography
- Inter / Geist / Clean modern sans-serif. Monospace for code snippets, IDs, and numeric metrics.
- Scales: 12px captions / tags, 14px dense table/list labels, 16px body copy, 20-24px section titles, 28-32px page headers.

## Spatial System & Radius
- 4px, 8px, 12px, 16px, 24px, 32px spacing scale.
- Card radius: 12-16px, Input/button radius: 8-10px, Pill radius: 9999px.
- Compact rail navigation: 72-80px width on desktop.
