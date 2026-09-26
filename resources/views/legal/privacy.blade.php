@php
    $retentionMonths = config('marketing.demo_requests.retention_months');
@endphp

<x-legal.page title="Politique de confidentialité" description="Comment VibeVault collecte, utilise et protège vos données personnelles, et comment exercer vos droits.">
    <p>Cette politique décrit les traitements de données personnelles réalisés sur le site vitrine de VibeVault. Elle est établie conformément au Règlement (UE) 2016/679 (RGPD) et à la loi n° 78-17 du 6 janvier 1978 modifiée.</p>

    <h2>Responsable du traitement</h2>
    <p><x-legal.value key="name" />, <x-legal.value key="address" />. Pour toute question relative à vos données : <x-legal.value key="privacy_email" />.</p>

    <h2>Données collectées via la demande de démo</h2>
    <ul>
        <li>Nom, email professionnel, nom du label ou de la structure et profil ;</li>
        <li>Taille du catalogue et message, si vous les renseignez ;</li>
        <li>Date et heure de votre consentement ;</li>
        <li>Une empreinte non réversible de votre adresse IP, utilisée uniquement pour limiter les envois abusifs. Votre adresse IP elle-même n’est pas conservée.</li>
    </ul>

    <h2>Finalités et bases légales</h2>
    <ul>
        <li>Répondre à votre demande et organiser la démonstration : votre consentement, que vous pouvez retirer à tout moment ;</li>
        <li>Protéger le formulaire contre les envois automatisés et abusifs : notre intérêt légitime à assurer la sécurité du service.</li>
    </ul>

    <h2>Durée de conservation</h2>
    <p>Les demandes de démo sont supprimées automatiquement {{ $retentionMonths }} mois après leur envoi, ou plus tôt si vous nous le demandez. Si vous devenez client, les données nécessaires sont reprises dans la relation contractuelle.</p>

    <h2>Destinataires</h2>
    <p>Seules les personnes de VibeVault chargées de vous répondre accèdent à vos données. Elles sont hébergées par <x-legal.value key="host_name" />. Elles ne sont ni vendues, ni louées, ni cédées à des tiers.</p>

    <h2>Cookies</h2>
    <p>Le site n’utilise que des cookies strictement nécessaires à son fonctionnement : un cookie de session et un jeton de protection contre la falsification des requêtes (CSRF). Ils ne servent ni à la mesure d’audience ni à la publicité et ne nécessitent donc pas votre consentement. Les polices de caractères sont hébergées sur nos serveurs : aucune requête n’est envoyée à un service tiers lors de votre visite.</p>

    <h2>Données traitées dans la plateforme</h2>
    <p>Pour les données saisies par un label dans la plateforme (catalogue, contrats, relevés, données des artistes), le label est responsable du traitement et VibeVault agit en qualité de sous-traitant, dans les conditions prévues par le contrat de service et son accord de traitement des données.</p>

    <h2>Vos droits</h2>
    <p>Vous disposez d’un droit d’accès, de rectification, d’effacement, de limitation, d’opposition et de portabilité de vos données, ainsi que du droit de retirer votre consentement et de définir des directives relatives à leur sort après votre décès. Pour les exercer, écrivez à <x-legal.value key="privacy_email" />. Nous répondons dans un délai d’un mois.</p>
    <p>Si vous estimez que vos droits ne sont pas respectés, vous pouvez introduire une réclamation auprès de la CNIL (<a href="https://www.cnil.fr" rel="noopener">www.cnil.fr</a>).</p>
</x-legal.page>
