<?php

$config = array(

	'admin' => array(
		'core:AdminPassword',
	),

	'default-sp' => array(
		'saml:SP',
		'entityID' => null,
		'idp' => 'https://login.aaiedu.hr/sso/saml2/idp/metadata.php',
		'discoURL' => null
        ),

	'fedlab-sp' => array(
                'saml:SP',
                'entityID' => null,
                'idp' => 'fed-lab.aaiedu.hr',
                'discoURL' => null
        )
);
