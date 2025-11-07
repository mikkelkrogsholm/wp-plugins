# SEO Cluster Links

WordPress plugin til at linke pillar posts og cluster posts sammen automatisk.

## Hvad gør pluginet?

Dette plugin hjælper dig med at opbygge en stærk intern link-struktur mellem dine pillar posts (hovedartikler) og cluster posts (relaterede artikler). Det forbedrer både SEO og brugeroplevelsen ved automatisk at vise relevante links.

## Features

- **Pillar Posts**: Markér hovedartikler som pillar posts
- **Cluster Posts**: Markér relaterede artikler og link dem til en pillar
- **Automatiske links**: Viser automatisk relaterede artikler nederst i indlægget
- **Cross-linking**: Cluster posts linker til pillar + andre clusters
- **Clean design**: Responsivt og nemt at style efter dit tema

## Installation

1. Upload mappen `seo-cluster-links` til `/wp-content/plugins/`
2. Aktivér pluginet gennem 'Plugins' menuen i WordPress
3. Start med at markere dine posts som Pillar eller Cluster

## Sådan bruger du det

### Opret en Pillar Post

1. Skriv eller rediger et blog indlæg
2. Find "SEO Cluster Settings" boksen i højre side
3. Vælg "Pillar Post"
4. Udgiv dit indlæg

### Opret en Cluster Post

1. Skriv eller rediger et blog indlæg
2. Find "SEO Cluster Settings" boksen i højre side
3. Vælg "Cluster Post"
4. Vælg hvilken Pillar Post denne hører til
5. Udgiv dit indlæg

### Hvad sker der automatisk?

**På Pillar Posts:**
- Viser en liste over alle relaterede cluster posts
- Opdateres automatisk når du tilføjer nye clusters

**På Cluster Posts:**
- Viser et fremhævet link tilbage til pillar posten
- Viser links til andre cluster posts i samme gruppe

## Shortcode

Hvis du vil placere links et bestemt sted i stedet for nederst:

```
[cluster_links]
```

## Tilpasning af styling

Pluginet inkluderer basis styling som fungerer med de fleste temaer. Hvis du vil tilpasse udseendet, kan du overskrive CSS i dit tema:

```css
/* Tilpas container */
.scl-links-container {
    background: #your-color;
    border-left-color: #your-accent;
}

/* Tilpas links */
.scl-links-list a {
    color: #your-link-color;
}
```

## Teknisk Info

- **Version**: 1.0.0
- **Kræver**: WordPress 5.0+
- **PHP Version**: 7.0+
- **Arkitektur**: KISS og DRY principper
- **Pattern**: Singleton pattern for alle klasser

## Struktur

```
seo-cluster-links/
├── seo-cluster-links.php      # Main plugin fil
├── includes/
│   ├── class-meta-boxes.php   # Admin meta boxes
│   └── class-link-display.php # Frontend display
└── assets/
    └── css/
        ├── admin.css          # Admin styling
        └── frontend.css       # Frontend styling
```

## Support

For fejl eller feature requests, opret en issue på GitHub.
