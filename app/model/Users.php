<?php

/**
 * Tahle třída by teda měla spravovat uživatele.
 */
class Users extends NObject {

    const
            COLUMN_EMAIL = 'email',
            COLUMN_VERIFIED = 'verified', //E-mailem verifnutý učet
            COLUMN_RIGHTS = 'rights', //Rights jsou práva uživatelů (příklad admin má rights = 10, základní uživatel rights = 1...
            COLUMN_REGION = 'region_id', // - kraj podle dostupné databáze
            COLUMN_TOWN = 'town_id',
            COLUMN_PASSWORD = 'password',
            COLUMN_ID = 'id_user',
            COLUMN_DATE = 'created_at',
            COLUMN_NICK = 'nickname',
            COLUMN_BIRTH = 'b_date',
            COLUMN_SEX = 'sex', // Může nabývat hodnot: Muž, žena
            COLUMN_STATUS = 'r_state', /* Může nabývat hodnot:
              svobodný
              svobodná
              ve vztahu
              ženatý
              vdaná
              rozvedený
              ovdovělý
              rozvedená
              ovdovělá
              složité */
            COLUMN_AGE = 'age',
            COLUMN_EYES = 'eyes', /*
              hnědé
              modré
              zelené
              šedé
              černé */
            COLUMN_HAIR = 'hair', /*
              černé
              červené
              hnědé
              blonďaté
              pleš
              zrzavé
              jiné */
            COLUMN_WEIGTH = 'weight', /*
              40-50
              50-60
              60-70
              70-80
              80-90
              90-100
              100-120
              120-140
              více než 140 */
            COLUMN_HEIGHT = 'height', /*
              140-150
              150-160
              160-170
              170-180
              180-190
              190-200
              200-210
              210-220 */
            COLUMN_INFO = 'info',
            COLUMN_PROFIL_PIC = 'profil_pic', //cesta k obrázku
            COLUMN_EROTIC_PIC = 'erotic_pic',
            COLUMN_TB_PROFIL_PIC = 'tb_profil_pic', //cesta k thumbnail obrázku
            COLUMN_TB_EROTIC_PIC = 'tb_erotic_pic',
            COLUMN_PLUS = 'verifiedPlus', // časem to bude zaplacení prémiového účtu()        

            /* Checkbox >> */
            COLUMN_ORAL = 'oral',
            COLUMN_SM = 'SM',
            COLUMN_THREE = 'threesome',
            COLUMN_ORGI = 'orgies',
            COLUMN_BOND = 'bondage',
            COLUMN_LICK = 'licking',
            COLUMN_CLASSIC = 'classic_sex',
            COLUMN_PERVERSION = 'perversion',
            COLUMN_LATEX = 'latex',
            COLUMN_BEHIND = 'from_behind',
            COLUMN_TOYS = 'toys',
            COLUMN_ANAL = 'anal',
            COLUMN_SMOKE = 'smoking',
            COLUMN_KISS = 'kissing',
            COLUMN_FIST = 'fisting';

    /** @var NConnection */
    private $database;

    public function __construct(NConnection $database) {
        $this->database = $database;
    }
    
    public function register($data) {
        
    }
}

?>
