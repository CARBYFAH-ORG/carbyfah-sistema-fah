// src/composables/usarApiPaises.js
import { ref } from 'vue'

export const usarApiPaises = () => {
    const cargando = ref(false)

    // BASE DE DATOS COMPLETA DE PAÍSES EN ESPAÑOL
    const paisesEspanol = {
        'AF': { nombre: 'Afganistán', oficial: 'República Islámica de Afganistán', moneda: 'Afgani afgano', telefono: '+93' },
        'AL': { nombre: 'Albania', oficial: 'República de Albania', moneda: 'Lek albanés', telefono: '+355' },
        'DZ': { nombre: 'Argelia', oficial: 'República Argelina Democrática y Popular', moneda: 'Dinar argelino', telefono: '+213' },
        'AD': { nombre: 'Andorra', oficial: 'Principado de Andorra', moneda: 'Euro', telefono: '+376' },
        'AO': { nombre: 'Angola', oficial: 'República de Angola', moneda: 'Kwanza angoleño', telefono: '+244' },
        'AR': { nombre: 'Argentina', oficial: 'República Argentina', moneda: 'Peso argentino', telefono: '+54' },
        'AM': { nombre: 'Armenia', oficial: 'República de Armenia', moneda: 'Dram armenio', telefono: '+374' },
        'AU': { nombre: 'Australia', oficial: 'Mancomunidad de Australia', moneda: 'Dólar australiano', telefono: '+61' },
        'AT': { nombre: 'Austria', oficial: 'República de Austria', moneda: 'Euro', telefono: '+43' },
        'AZ': { nombre: 'Azerbaiyán', oficial: 'República de Azerbaiyán', moneda: 'Manat azerbaiyano', telefono: '+994' },
        'BS': { nombre: 'Bahamas', oficial: 'Mancomunidad de las Bahamas', moneda: 'Dólar bahameño', telefono: '+1242' },
        'BH': { nombre: 'Baréin', oficial: 'Reino de Baréin', moneda: 'Dinar bahreiní', telefono: '+973' },
        'BD': { nombre: 'Bangladesh', oficial: 'República Popular de Bangladesh', moneda: 'Taka bangladesí', telefono: '+880' },
        'BB': { nombre: 'Barbados', oficial: 'Barbados', moneda: 'Dólar barbadense', telefono: '+1246' },
        'BY': { nombre: 'Bielorrusia', oficial: 'República de Bielorrusia', moneda: 'Rublo bielorruso', telefono: '+375' },
        'BE': { nombre: 'Bélgica', oficial: 'Reino de Bélgica', moneda: 'Euro', telefono: '+32' },
        'BZ': { nombre: 'Belice', oficial: 'Belice', moneda: 'Dólar beliceño', telefono: '+501' },
        'BJ': { nombre: 'Benín', oficial: 'República de Benín', moneda: 'Franco CFA', telefono: '+229' },
        'BT': { nombre: 'Bután', oficial: 'Reino de Bután', moneda: 'Ngultrum butanés', telefono: '+975' },
        'BO': { nombre: 'Bolivia', oficial: 'Estado Plurinacional de Bolivia', moneda: 'Boliviano', telefono: '+591' },
        'BA': { nombre: 'Bosnia y Herzegovina', oficial: 'Bosnia y Herzegovina', moneda: 'Marco convertible', telefono: '+387' },
        'BW': { nombre: 'Botsuana', oficial: 'República de Botsuana', moneda: 'Pula botsuano', telefono: '+267' },
        'BR': { nombre: 'Brasil', oficial: 'República Federativa del Brasil', moneda: 'Real brasileño', telefono: '+55' },
        'BN': { nombre: 'Brunéi', oficial: 'Sultanato de Brunéi', moneda: 'Dólar de Brunéi', telefono: '+673' },
        'BG': { nombre: 'Bulgaria', oficial: 'República de Bulgaria', moneda: 'Lev búlgaro', telefono: '+359' },
        'BF': { nombre: 'Burkina Faso', oficial: 'Burkina Faso', moneda: 'Franco CFA', telefono: '+226' },
        'BI': { nombre: 'Burundi', oficial: 'República de Burundi', moneda: 'Franco burundés', telefono: '+257' },
        'CV': { nombre: 'Cabo Verde', oficial: 'República de Cabo Verde', moneda: 'Escudo caboverdiano', telefono: '+238' },
        'KH': { nombre: 'Camboya', oficial: 'Reino de Camboya', moneda: 'Riel camboyano', telefono: '+855' },
        'CM': { nombre: 'Camerún', oficial: 'República de Camerún', moneda: 'Franco CFA', telefono: '+237' },
        'CA': { nombre: 'Canadá', oficial: 'Canadá', moneda: 'Dólar canadiense', telefono: '+1' },
        'CF': { nombre: 'República Centroafricana', oficial: 'República Centroafricana', moneda: 'Franco CFA', telefono: '+236' },
        'TD': { nombre: 'Chad', oficial: 'República de Chad', moneda: 'Franco CFA', telefono: '+235' },
        'CL': { nombre: 'Chile', oficial: 'República de Chile', moneda: 'Peso chileno', telefono: '+56' },
        'CN': { nombre: 'China', oficial: 'República Popular China', moneda: 'Yuan chino', telefono: '+86' },
        'CO': { nombre: 'Colombia', oficial: 'República de Colombia', moneda: 'Peso colombiano', telefono: '+57' },
        'KM': { nombre: 'Comoras', oficial: 'Unión de las Comoras', moneda: 'Franco comorense', telefono: '+269' },
        'CG': { nombre: 'Congo', oficial: 'República del Congo', moneda: 'Franco CFA', telefono: '+242' },
        'CD': { nombre: 'República Democrática del Congo', oficial: 'República Democrática del Congo', moneda: 'Franco congoleño', telefono: '+243' },
        'CR': { nombre: 'Costa Rica', oficial: 'República de Costa Rica', moneda: 'Colón costarricense', telefono: '+506' },
        'CI': { nombre: 'Costa de Marfil', oficial: 'República de Costa de Marfil', moneda: 'Franco CFA', telefono: '+225' },
        'HR': { nombre: 'Croacia', oficial: 'República de Croacia', moneda: 'Euro', telefono: '+385' },
        'CU': { nombre: 'Cuba', oficial: 'República de Cuba', moneda: 'Peso cubano', telefono: '+53' },
        'CY': { nombre: 'Chipre', oficial: 'República de Chipre', moneda: 'Euro', telefono: '+357' },
        'CZ': { nombre: 'República Checa', oficial: 'República Checa', moneda: 'Corona checa', telefono: '+420' },
        'DK': { nombre: 'Dinamarca', oficial: 'Reino de Dinamarca', moneda: 'Corona danesa', telefono: '+45' },
        'DJ': { nombre: 'Yibuti', oficial: 'República de Yibuti', moneda: 'Franco yibutiano', telefono: '+253' },
        'DM': { nombre: 'Dominica', oficial: 'Mancomunidad de Dominica', moneda: 'Dólar del Caribe Oriental', telefono: '+1767' },
        'DO': { nombre: 'República Dominicana', oficial: 'República Dominicana', moneda: 'Peso dominicano', telefono: '+1809' },
        'EC': { nombre: 'Ecuador', oficial: 'República del Ecuador', moneda: 'Dólar estadounidense', telefono: '+593' },
        'EG': { nombre: 'Egipto', oficial: 'República Árabe de Egipto', moneda: 'Libra egipcia', telefono: '+20' },
        'SV': { nombre: 'El Salvador', oficial: 'República de El Salvador', moneda: 'Dólar estadounidense', telefono: '+503' },
        'GQ': { nombre: 'Guinea Ecuatorial', oficial: 'República de Guinea Ecuatorial', moneda: 'Franco CFA', telefono: '+240' },
        'ER': { nombre: 'Eritrea', oficial: 'Estado de Eritrea', moneda: 'Nakfa eritreo', telefono: '+291' },
        'EE': { nombre: 'Estonia', oficial: 'República de Estonia', moneda: 'Euro', telefono: '+372' },
        'SZ': { nombre: 'Esuatini', oficial: 'Reino de Esuatini', moneda: 'Lilangeni', telefono: '+268' },
        'ET': { nombre: 'Etiopía', oficial: 'República Democrática Federal de Etiopía', moneda: 'Birr etíope', telefono: '+251' },
        'FJ': { nombre: 'Fiyi', oficial: 'República de Fiyi', moneda: 'Dólar fiyiano', telefono: '+679' },
        'FI': { nombre: 'Finlandia', oficial: 'República de Finlandia', moneda: 'Euro', telefono: '+358' },
        'FR': { nombre: 'Francia', oficial: 'República Francesa', moneda: 'Euro', telefono: '+33' },
        'GA': { nombre: 'Gabón', oficial: 'República Gabonesa', moneda: 'Franco CFA', telefono: '+241' },
        'GM': { nombre: 'Gambia', oficial: 'República de Gambia', moneda: 'Dalasi gambiano', telefono: '+220' },
        'GE': { nombre: 'Georgia', oficial: 'Georgia', moneda: 'Lari georgiano', telefono: '+995' },
        'DE': { nombre: 'Alemania', oficial: 'República Federal de Alemania', moneda: 'Euro', telefono: '+49' },
        'GH': { nombre: 'Ghana', oficial: 'República de Ghana', moneda: 'Cedi ghanés', telefono: '+233' },
        'GR': { nombre: 'Grecia', oficial: 'República Helénica', moneda: 'Euro', telefono: '+30' },
        'GD': { nombre: 'Granada', oficial: 'Granada', moneda: 'Dólar del Caribe Oriental', telefono: '+1473' },
        'GT': { nombre: 'Guatemala', oficial: 'República de Guatemala', moneda: 'Quetzal guatemalteco', telefono: '+502' },
        'GN': { nombre: 'Guinea', oficial: 'República de Guinea', moneda: 'Franco guineano', telefono: '+224' },
        'GW': { nombre: 'Guinea-Bisáu', oficial: 'República de Guinea-Bisáu', moneda: 'Franco CFA', telefono: '+245' },
        'GY': { nombre: 'Guyana', oficial: 'República Cooperativa de Guyana', moneda: 'Dólar guyanés', telefono: '+592' },
        'HT': { nombre: 'Haití', oficial: 'República de Haití', moneda: 'Gourde haitiano', telefono: '+509' },
        'HN': { nombre: 'Honduras', oficial: 'República de Honduras', moneda: 'Lempira', telefono: '+504' },
        'HU': { nombre: 'Hungría', oficial: 'Hungría', moneda: 'Forinto húngaro', telefono: '+36' },
        'IS': { nombre: 'Islandia', oficial: 'República de Islandia', moneda: 'Corona islandesa', telefono: '+354' },
        'IN': { nombre: 'India', oficial: 'República de la India', moneda: 'Rupia india', telefono: '+91' },
        'ID': { nombre: 'Indonesia', oficial: 'República de Indonesia', moneda: 'Rupia indonesia', telefono: '+62' },
        'IR': { nombre: 'Irán', oficial: 'República Islámica de Irán', moneda: 'Rial iraní', telefono: '+98' },
        'IQ': { nombre: 'Irak', oficial: 'República de Irak', moneda: 'Dinar iraquí', telefono: '+964' },
        'IE': { nombre: 'Irlanda', oficial: 'República de Irlanda', moneda: 'Euro', telefono: '+353' },
        'IL': { nombre: 'Israel', oficial: 'Estado de Israel', moneda: 'Nuevo shéquel israelí', telefono: '+972' },
        'IT': { nombre: 'Italia', oficial: 'República Italiana', moneda: 'Euro', telefono: '+39' },
        'JM': { nombre: 'Jamaica', oficial: 'Jamaica', moneda: 'Dólar jamaicano', telefono: '+1876' },
        'JP': { nombre: 'Japón', oficial: 'Japón', moneda: 'Yen japonés', telefono: '+81' },
        'JO': { nombre: 'Jordania', oficial: 'Reino Hachemita de Jordania', moneda: 'Dinar jordano', telefono: '+962' },
        'KZ': { nombre: 'Kazajistán', oficial: 'República de Kazajistán', moneda: 'Tenge kazajo', telefono: '+7' },
        'KE': { nombre: 'Kenia', oficial: 'República de Kenia', moneda: 'Chelín keniano', telefono: '+254' },
        'KI': { nombre: 'Kiribati', oficial: 'República de Kiribati', moneda: 'Dólar australiano', telefono: '+686' },
        'KP': { nombre: 'Corea del Norte', oficial: 'República Popular Democrática de Corea', moneda: 'Won norcoreano', telefono: '+850' },
        'KR': { nombre: 'Corea del Sur', oficial: 'República de Corea', moneda: 'Won surcoreano', telefono: '+82' },
        'KW': { nombre: 'Kuwait', oficial: 'Estado de Kuwait', moneda: 'Dinar kuwaití', telefono: '+965' },
        'KG': { nombre: 'Kirguistán', oficial: 'República Kirguisa', moneda: 'Som kirguís', telefono: '+996' },
        'LA': { nombre: 'Laos', oficial: 'República Democrática Popular Lao', moneda: 'Kip laosiano', telefono: '+856' },
        'LV': { nombre: 'Letonia', oficial: 'República de Letonia', moneda: 'Euro', telefono: '+371' },
        'LB': { nombre: 'Líbano', oficial: 'República Libanesa', moneda: 'Libra libanesa', telefono: '+961' },
        'LS': { nombre: 'Lesoto', oficial: 'Reino de Lesoto', moneda: 'Loti lesotense', telefono: '+266' },
        'LR': { nombre: 'Liberia', oficial: 'República de Liberia', moneda: 'Dólar liberiano', telefono: '+231' },
        'LY': { nombre: 'Libia', oficial: 'Estado de Libia', moneda: 'Dinar libio', telefono: '+218' },
        'LI': { nombre: 'Liechtenstein', oficial: 'Principado de Liechtenstein', moneda: 'Franco suizo', telefono: '+423' },
        'LT': { nombre: 'Lituania', oficial: 'República de Lituania', moneda: 'Euro', telefono: '+370' },
        'LU': { nombre: 'Luxemburgo', oficial: 'Gran Ducado de Luxemburgo', moneda: 'Euro', telefono: '+352' },
        'MG': { nombre: 'Madagascar', oficial: 'República de Madagascar', moneda: 'Ariary malgache', telefono: '+261' },
        'MW': { nombre: 'Malaui', oficial: 'República de Malaui', moneda: 'Kwacha malauí', telefono: '+265' },
        'MY': { nombre: 'Malasia', oficial: 'Malasia', moneda: 'Ringgit malayo', telefono: '+60' },
        'MV': { nombre: 'Maldivas', oficial: 'República de Maldivas', moneda: 'Rufiyaa maldiva', telefono: '+960' },
        'ML': { nombre: 'Malí', oficial: 'República de Malí', moneda: 'Franco CFA', telefono: '+223' },
        'MT': { nombre: 'Malta', oficial: 'República de Malta', moneda: 'Euro', telefono: '+356' },
        'MH': { nombre: 'Islas Marshall', oficial: 'República de las Islas Marshall', moneda: 'Dólar estadounidense', telefono: '+692' },
        'MR': { nombre: 'Mauritania', oficial: 'República Islámica de Mauritania', moneda: 'Ouguiya mauritana', telefono: '+222' },
        'MU': { nombre: 'Mauricio', oficial: 'República de Mauricio', moneda: 'Rupia mauriciana', telefono: '+230' },
        'MX': { nombre: 'México', oficial: 'Estados Unidos Mexicanos', moneda: 'Peso mexicano', telefono: '+52' },
        'FM': { nombre: 'Micronesia', oficial: 'Estados Federados de Micronesia', moneda: 'Dólar estadounidense', telefono: '+691' },
        'MD': { nombre: 'Moldavia', oficial: 'República de Moldavia', moneda: 'Leu moldavo', telefono: '+373' },
        'MC': { nombre: 'Mónaco', oficial: 'Principado de Mónaco', moneda: 'Euro', telefono: '+377' },
        'MN': { nombre: 'Mongolia', oficial: 'Mongolia', moneda: 'Tugrik mongol', telefono: '+976' },
        'ME': { nombre: 'Montenegro', oficial: 'Montenegro', moneda: 'Euro', telefono: '+382' },
        'MA': { nombre: 'Marruecos', oficial: 'Reino de Marruecos', moneda: 'Dirham marroquí', telefono: '+212' },
        'MZ': { nombre: 'Mozambique', oficial: 'República de Mozambique', moneda: 'Metical mozambiqueño', telefono: '+258' },
        'MM': { nombre: 'Myanmar', oficial: 'República de la Unión de Myanmar', moneda: 'Kyat birmano', telefono: '+95' },
        'NA': { nombre: 'Namibia', oficial: 'República de Namibia', moneda: 'Dólar namibio', telefono: '+264' },
        'NR': { nombre: 'Nauru', oficial: 'República de Nauru', moneda: 'Dólar australiano', telefono: '+674' },
        'NP': { nombre: 'Nepal', oficial: 'República Federal Democrática de Nepal', moneda: 'Rupia nepalí', telefono: '+977' },
        'NL': { nombre: 'Países Bajos', oficial: 'Reino de los Países Bajos', moneda: 'Euro', telefono: '+31' },
        'NZ': { nombre: 'Nueva Zelanda', oficial: 'Nueva Zelanda', moneda: 'Dólar neozelandés', telefono: '+64' },
        'NI': { nombre: 'Nicaragua', oficial: 'República de Nicaragua', moneda: 'Córdoba nicaragüense', telefono: '+505' },
        'NE': { nombre: 'Níger', oficial: 'República de Níger', moneda: 'Franco CFA', telefono: '+227' },
        'NG': { nombre: 'Nigeria', oficial: 'República Federal de Nigeria', moneda: 'Naira nigeriana', telefono: '+234' },
        'MK': { nombre: 'Macedonia del Norte', oficial: 'República de Macedonia del Norte', moneda: 'Denar macedonio', telefono: '+389' },
        'NO': { nombre: 'Noruega', oficial: 'Reino de Noruega', moneda: 'Corona noruega', telefono: '+47' },
        'OM': { nombre: 'Omán', oficial: 'Sultanato de Omán', moneda: 'Rial omaní', telefono: '+968' },
        'PK': { nombre: 'Pakistán', oficial: 'República Islámica de Pakistán', moneda: 'Rupia pakistaní', telefono: '+92' },
        'PW': { nombre: 'Palaos', oficial: 'República de Palaos', moneda: 'Dólar estadounidense', telefono: '+680' },
        'PA': { nombre: 'Panamá', oficial: 'República de Panamá', moneda: 'Balboa panameña', telefono: '+507' },
        'PG': { nombre: 'Papúa Nueva Guinea', oficial: 'Estado Independiente de Papúa Nueva Guinea', moneda: 'Kina papúa', telefono: '+675' },
        'PY': { nombre: 'Paraguay', oficial: 'República del Paraguay', moneda: 'Guaraní paraguayo', telefono: '+595' },
        'PE': { nombre: 'Perú', oficial: 'República del Perú', moneda: 'Sol peruano', telefono: '+51' },
        'PH': { nombre: 'Filipinas', oficial: 'República de Filipinas', moneda: 'Peso filipino', telefono: '+63' },
        'PL': { nombre: 'Polonia', oficial: 'República de Polonia', moneda: 'Zloty polaco', telefono: '+48' },
        'PT': { nombre: 'Portugal', oficial: 'República Portuguesa', moneda: 'Euro', telefono: '+351' },
        'QA': { nombre: 'Catar', oficial: 'Estado de Catar', moneda: 'Riyal catarí', telefono: '+974' },
        'RO': { nombre: 'Rumania', oficial: 'Rumania', moneda: 'Leu rumano', telefono: '+40' },
        'RU': { nombre: 'Rusia', oficial: 'Federación Rusa', moneda: 'Rublo ruso', telefono: '+7' },
        'RW': { nombre: 'Ruanda', oficial: 'República de Ruanda', moneda: 'Franco ruandés', telefono: '+250' },
        'KN': { nombre: 'San Cristóbal y Nieves', oficial: 'Federación de San Cristóbal y Nieves', moneda: 'Dólar del Caribe Oriental', telefono: '+1869' },
        'LC': { nombre: 'Santa Lucía', oficial: 'Santa Lucía', moneda: 'Dólar del Caribe Oriental', telefono: '+1758' },
        'VC': { nombre: 'San Vicente y las Granadinas', oficial: 'San Vicente y las Granadinas', moneda: 'Dólar del Caribe Oriental', telefono: '+1784' },
        'WS': { nombre: 'Samoa', oficial: 'Estado Independiente de Samoa', moneda: 'Tala samoana', telefono: '+685' },
        'SM': { nombre: 'San Marino', oficial: 'República de San Marino', moneda: 'Euro', telefono: '+378' },
        'ST': { nombre: 'Santo Tomé y Príncipe', oficial: 'República Democrática de Santo Tomé y Príncipe', moneda: 'Dobra santotomense', telefono: '+239' },
        'SA': { nombre: 'Arabia Saudí', oficial: 'Reino de Arabia Saudí', moneda: 'Riyal saudí', telefono: '+966' },
        'SN': { nombre: 'Senegal', oficial: 'República de Senegal', moneda: 'Franco CFA', telefono: '+221' },
        'RS': { nombre: 'Serbia', oficial: 'República de Serbia', moneda: 'Dinar serbio', telefono: '+381' },
        'SC': { nombre: 'Seychelles', oficial: 'República de Seychelles', moneda: 'Rupia de Seychelles', telefono: '+248' },
        'SL': { nombre: 'Sierra Leona', oficial: 'República de Sierra Leona', moneda: 'Leone de Sierra Leona', telefono: '+232' },
        'SG': { nombre: 'Singapur', oficial: 'República de Singapur', moneda: 'Dólar de Singapur', telefono: '+65' },
        'SK': { nombre: 'Eslovaquia', oficial: 'República Eslovaca', moneda: 'Euro', telefono: '+421' },
        'SI': { nombre: 'Eslovenia', oficial: 'República de Eslovenia', moneda: 'Euro', telefono: '+386' },
        'SB': { nombre: 'Islas Salomón', oficial: 'Islas Salomón', moneda: 'Dólar de las Islas Salomón', telefono: '+677' },
        'SO': { nombre: 'Somalia', oficial: 'República Federal de Somalia', moneda: 'Chelín somalí', telefono: '+252' },
        'ZA': { nombre: 'Sudáfrica', oficial: 'República de Sudáfrica', moneda: 'Rand sudafricano', telefono: '+27' },
        'SS': { nombre: 'Sudán del Sur', oficial: 'República de Sudán del Sur', moneda: 'Libra sursudanesa', telefono: '+211' },
        'ES': { nombre: 'España', oficial: 'Reino de España', moneda: 'Euro', telefono: '+34' },
        'LK': { nombre: 'Sri Lanka', oficial: 'República Socialista Democrática de Sri Lanka', moneda: 'Rupia de Sri Lanka', telefono: '+94' },
        'SD': { nombre: 'Sudán', oficial: 'República de Sudán', moneda: 'Libra sudanesa', telefono: '+249' },
        'SR': { nombre: 'Surinam', oficial: 'República de Surinam', moneda: 'Dólar surinamés', telefono: '+597' },
        'SE': { nombre: 'Suecia', oficial: 'Reino de Suecia', moneda: 'Corona sueca', telefono: '+46' },
        'CH': { nombre: 'Suiza', oficial: 'Confederación Suiza', moneda: 'Franco suizo', telefono: '+41' },
        'SY': { nombre: 'Siria', oficial: 'República Árabe Siria', moneda: 'Libra siria', telefono: '+963' },
        'TJ': { nombre: 'Tayikistán', oficial: 'República de Tayikistán', moneda: 'Somoni tayiko', telefono: '+992' },
        'TZ': { nombre: 'Tanzania', oficial: 'República Unida de Tanzania', moneda: 'Chelín tanzano', telefono: '+255' },
        'TH': { nombre: 'Tailandia', oficial: 'Reino de Tailandia', moneda: 'Baht tailandés', telefono: '+66' },
        'TL': { nombre: 'Timor Oriental', oficial: 'República Democrática de Timor Oriental', moneda: 'Dólar estadounidense', telefono: '+670' },
        'TG': { nombre: 'Togo', oficial: 'República Togolesa', moneda: 'Franco CFA', telefono: '+228' },
        'TO': { nombre: 'Tonga', oficial: 'Reino de Tonga', moneda: 'Paʻanga tongana', telefono: '+676' },
        'TT': { nombre: 'Trinidad y Tobago', oficial: 'República de Trinidad y Tobago', moneda: 'Dólar de Trinidad y Tobago', telefono: '+1868' },
        'TN': { nombre: 'Túnez', oficial: 'República Tunecina', moneda: 'Dinar tunecino', telefono: '+216' },
        'TR': { nombre: 'Turquía', oficial: 'República de Turquía', moneda: 'Lira turca', telefono: '+90' },
        'TM': { nombre: 'Turkmenistán', oficial: 'Turkmenistán', moneda: 'Manat turcomano', telefono: '+993' },
        'TV': { nombre: 'Tuvalu', oficial: 'Tuvalu', moneda: 'Dólar australiano', telefono: '+688' },
        'UG': { nombre: 'Uganda', oficial: 'República de Uganda', moneda: 'Chelín ugandés', telefono: '+256' },
        'UA': { nombre: 'Ucrania', oficial: 'Ucrania', moneda: 'Grivna ucraniana', telefono: '+380' },
        'AE': { nombre: 'Emiratos Árabes Unidos', oficial: 'Emiratos Árabes Unidos', moneda: 'Dirham de los Emiratos Árabes Unidos', telefono: '+971' },
        'GB': { nombre: 'Reino Unido', oficial: 'Reino Unido de Gran Bretaña e Irlanda del Norte', moneda: 'Libra esterlina', telefono: '+44' },
        'US': { nombre: 'Estados Unidos', oficial: 'Estados Unidos de América', moneda: 'Dólar estadounidense', telefono: '+1' },
        'UY': { nombre: 'Uruguay', oficial: 'República Oriental del Uruguay', moneda: 'Peso uruguayo', telefono: '+598' },
        'UZ': { nombre: 'Uzbekistán', oficial: 'República de Uzbekistán', moneda: 'Som uzbeko', telefono: '+998' },
        'VU': { nombre: 'Vanuatu', oficial: 'República de Vanuatu', moneda: 'Vatu vanuatuense', telefono: '+678' },
        'VA': { nombre: 'Ciudad del Vaticano', oficial: 'Estado de la Ciudad del Vaticano', moneda: 'Euro', telefono: '+379' },
        'VE': { nombre: 'Venezuela', oficial: 'República Bolivariana de Venezuela', moneda: 'Bolívar venezolano', telefono: '+58' },
        'VN': { nombre: 'Vietnam', oficial: 'República Socialista de Vietnam', moneda: 'Dong vietnamita', telefono: '+84' },
        'YE': { nombre: 'Yemen', oficial: 'República de Yemen', moneda: 'Rial yemení', telefono: '+967' },
        'ZM': { nombre: 'Zambia', oficial: 'República de Zambia', moneda: 'Kwacha zambiano', telefono: '+260' },
        'ZW': { nombre: 'Zimbabue', oficial: 'República de Zimbabue', moneda: 'Dólar zimbabuense', telefono: '+263' }
    }

    const buscarPaisesPorNombre = async (nombre) => {
        if (!nombre || nombre.length < 2) {
            return []
        }

        cargando.value = true

        try {
            // USAR REST COUNTRIES PARA OBTENER CÓDIGOS ISO
            const response = await fetch(
                `https://restcountries.com/v3.1/name/${nombre}?fields=name,cca2,cca3,idd,currencies,translations`
            )

            if (!response.ok) {
                throw new Error('Error al buscar países')
            }

            const paises = await response.json()

            return paises.map(pais => {
                // BUSCAR EN NUESTRA BASE DE DATOS COMPLETA
                const codigoISO2 = pais.cca2
                const datosEspanol = paisesEspanol[codigoISO2]

                if (datosEspanol) {
                    // DATOS COMPLETOS EN ESPAÑOL
                    return {
                        nombre: datosEspanol.nombre,
                        nombreOficial: datosEspanol.oficial,
                        codigoISO3: pais.cca3,
                        codigoTelefono: datosEspanol.telefono,
                        monedaOficial: datosEspanol.moneda
                    }
                } else {
                    // FALLBACK CON TRADUCCIÓN DE LA API
                    const nombreEspanol = pais.translations?.spa?.common || pais.name.common
                    const nombreOficialEspanol = pais.translations?.spa?.official || pais.name.official
                    const monedaOriginal = Object.values(pais.currencies || {})[0]?.name || ''

                    return {
                        nombre: nombreEspanol,
                        nombreOficial: nombreOficialEspanol,
                        codigoISO3: pais.cca3,
                        codigoTelefono: pais.idd?.root ? `${pais.idd.root}${pais.idd.suffixes?.[0] || ''}` : '',
                        monedaOficial: monedaOriginal
                    }
                }
            })

        } catch (error) {
            console.error('Error buscando países:', error)
            return []
        } finally {
            cargando.value = false
        }
    }

    return {
        buscarPaisesPorNombre,
        cargando
    }
}