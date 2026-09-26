<x-legal.page title="Conditions générales d’utilisation" description="Conditions générales d’utilisation du site vitrine VibeVault.">
    <h2>Objet</h2>
    <p>Les présentes conditions encadrent l’accès et l’utilisation du site vitrine de VibeVault, édité par <x-legal.value key="name" />. L’utilisation de la plateforme par les labels et leurs artistes est régie par un contrat de service distinct, conclu lors de la souscription.</p>

    <h2>Accès au site</h2>
    <p>Le site est accessible gratuitement à toute personne disposant d’un accès à internet. L’éditeur peut en suspendre l’accès, notamment pour maintenance, sans que sa responsabilité puisse être engagée.</p>

    <h2>Utilisation du formulaire de demande de démo</h2>
    <p>Vous vous engagez à fournir des informations exactes et à ne pas utiliser le formulaire à des fins de prospection, d’envoi massif ou de manière automatisée. Les envois sont limités en nombre pour prévenir les abus.</p>

    <h2>Inscription et abonnement en ligne</h2>
    <p>La création d’un compte en ligne ouvre un espace pour votre label et un abonnement à la formule choisie. La personne qui s’inscrit devient propriétaire du label et déclare agir pour le compte de la structure concernée.</p>
    <ul>
        <li>Le premier abonnement d’un label commence par une période d’essai gratuite de {{ config('marketing.trial_days') }} jours. Un moyen de paiement est demandé à l’inscription ; aucun montant n’est prélevé avant la fin de l’essai.</li>
        <li>Sauf résiliation avant la fin de l’essai, l’abonnement se renouvelle automatiquement à chaque échéance, mensuelle ou annuelle selon la formule choisie.</li>
        <li>Les prix sont indiqués hors taxes. La TVA applicable est calculée lors du paiement ; les clients professionnels établis dans un autre État membre de l’Union européenne peuvent renseigner leur numéro de TVA intracommunautaire pour bénéficier de l’autoliquidation.</li>
        <li>Le paiement, les factures et la résiliation sont gérés depuis la page Facturation de l’espace label. La résiliation prend effet à la fin de la période en cours ; l’accès est maintenu jusqu’à cette date.</li>
        <li>En cas d’échec de paiement, de nouvelles tentatives sont effectuées automatiquement. Si le paiement ne peut aboutir, l’accès à l’espace label est suspendu jusqu’à régularisation.</li>
    </ul>

    <h2>Informations présentées</h2>
    <p>Les descriptions des fonctionnalités et les tarifs sont donnés à titre indicatif et peuvent évoluer. Seules les conditions figurant dans le contrat de service engagent l’éditeur. Les exemples de relevés, de titres et d’artistes sont fictifs.</p>

    <h2>Propriété intellectuelle</h2>
    <p>Les contenus du site sont protégés. Leur reproduction est soumise aux conditions décrites dans les <a href="{{ route('legal.notice') }}">mentions légales</a>.</p>

    <h2>Responsabilité</h2>
    <p>L’éditeur met en œuvre les moyens raisonnables pour assurer l’exactitude des informations du site, sans pouvoir garantir qu’elles sont exemptes d’erreurs. Les liens vers des sites tiers n’engagent pas sa responsabilité quant à leur contenu.</p>

    <h2>Données personnelles</h2>
    <p>Voir la <a href="{{ route('legal.privacy') }}">politique de confidentialité</a>.</p>

    <h2>Droit applicable</h2>
    <p>Les présentes conditions sont soumises au droit français. En cas de litige, et à défaut de résolution amiable, les tribunaux français sont compétents.</p>
</x-legal.page>
