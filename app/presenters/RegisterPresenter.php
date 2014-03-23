<?php

use Nette\Application\UI,
    Nette\Application\UI\Form as Form;

class RegisterPresenter extends BasePresenter {

    /** @var Users */
    private $users;

    protected function startup() {
        parent::startup();
        $this->users = $this->context->users;
    }

    public function renderRegister() {
        
    }

    /**
     * Formulář pro registraci uživatele.
     * @return \Nette\Application\UI\Form
     */
    protected function createComponentRegisterForm() {
        $form = new Form;
        $form->addText('nickname', 'Uživatelské jméno');
        $form->addText('email', 'E-mail: *', 35)
                ->setEmptyValue('@')
                ->addRule(Form::FILLED, 'Vyplňte Váš email')
                ->addCondition(Form::FILLED)
                ->addRule(Form::EMAIL, 'Neplatná emailová adresa');
        $form->addPassword('password', 'Heslo: *', 20)
                ->setOption('description', 'Alespoň 6 znaků')
                ->addRule(Form::FILLED, 'Vyplňte Vaše heslo')
                ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků.', 6);
        $form->addPassword('password2', 'Heslo znovu: *', 20)
                ->addConditionOn($form['password'], Form::VALID)
                ->addRule(Form::FILLED, 'Heslo znovu')
                ->addRule(Form::EQUAL, 'Hesla se neshodují.', $form['password']);
        $form->addTextArea('info', 'Něco o vás:');
        $form->addText('age', 'Věk:')
                ->setType('number')
                ->addRule(Form::INTEGER, 'Věk musí být číslo')
                ->addRule(Form::RANGE, 'Věk musí být od 18 do 120', array(18, 120));
        $sex = array(
            'm' => 'muž',
            'f' => 'žena',
        );
        $form->addRadioList('gender', 'Pohlaví:', $sex);
        
        
        $regions = array(                     //Tady budou Kraje z databáze
            'Europe' => array(
                'CZ' => 'Česká Republika',
                'SK' => 'Slovensko',
                'GB' => 'Velká Británie',
            ),
            'CA' => 'Kanada',
            'US' => 'USA',
            '?' => 'jiná',
        );

        $form->addSelect('region', 'Kraj:', $regions)
                ->setPrompt('Vyberte kraj');
        
        $towns = array(                     //Tady budou Města z databáze
            'Europe' => array(
                'CZ' => 'Česká Republika',
                'SK' => 'Slovensko',
                'GB' => 'Velká Británie',
            ),
            'CA' => 'Kanada',
            'US' => 'USA',
            '?' => 'jiná',
        );

        $form->addSelect('town', 'Město:', $towns)
                ->setPrompt('Zvolte město');
        
        $r_state = array(                    
             'svobodný' => 'svobodný',
             'svobodná' => 'svobodná',
             've vztahu' => 've vztahu',
             'ženatý' => 'ženatý',
             'vdaná' => 'vdaná',
             'rozvedený' => 'rozvedený',
            'ovdovělý'  => 'ovdovělý',
           'rozvedená'   => 'rozvedená',
          'ovdovělá'    => 'ovdovělá',
           'složité'   => 'složité'
        );

        $form->addSelect('r_state', 'Osobní stav:', $r_state)
                ->setPrompt('Zvolte status');
        
        
        $weight = array(                     //Tady budou Města z databáze
              '40-50',
              '50-60',
              '60-70',
              '70-80',
              '80-90',
              '90-100',
              '100-120',
              '120-140',
              'více než 140',
        );

        $form->addSelect('town', 'Vašeí váha:', $weight)
                ->setPrompt('Zvolte váhu');
        
        
        $height = array(                     //Tady budou Města z databáze
              '140-150',
              '150-160',
              '160-170',
              '170-180',
              '180-190',
              '190-200',
              '200-210',
              '210-220');

        $form->addSelect('height', 'Výška:', $height)
                ->setPrompt('Zvolte výšku');
        
        $hair = array(                     //Tady budou Města z databáze
              'černé',
              'červené',
              'hnědé',
              'blonďaté',
              'pleš',
              'zrzavé',
              'jiné');

        $form->addSelect('hair', 'Barva vlasů:', $hair)
                ->setPrompt('Zvolte barvu vlasů');
        
        $eyes = array(                     //Tady budou Města z databáze
              'hnědé',
              'modré',
              'zelené',
              'šedé',
              'černé');

        $form->addSelect('eyes', 'Barva očí:', $eyes)
                ->setPrompt('Zvolte barvu očí');
        
        
        $form->addCheckbox('oral', 'Orál');
        $form->addCheckbox('SM', 'SM');
        $form->addCheckbox('threesome', 'Trojka');
        $form->addCheckbox('orgies', 'Orgie');
        $form->addCheckbox('bondage', 'Svazování');
        $form->addCheckbox('licking', 'Lízání');
        $form->addCheckbox('classic_sex', 'Klasický sex');
        $form->addCheckbox('perversion', 'Perverze');
        $form->addCheckbox('from_behind', 'Ze zadu');
        $form->addCheckbox('toys', 'Hračky');
        $form->addCheckbox('anal', 'Análek');
        $form->addCheckbox('smoking', 'Kouření');
        $form->addCheckbox('kissing', 'Líbání');
        $form->addCheckbox('fisting', 'Fisting');
        
        $form->addCheckbox('agree', 'Souhlasím s podmínkami')
                ->addRule(Form::EQUAL, 'Je potřeba souhlasit s podmínkami', TRUE);
        $form->addSubmit('send', 'Registrovat');
        $form->onSuccess[] = callback($this, 'registerFormSubmitted');
        return $form;
    }

    public function registerFormSubmitted(UI\Form $form) {
        $values = $form->getValues();
        $new_user_id = $this->users->register($values);
        if ($new_user_id) {
            $this->flashMessage('Registrace se zdařila, jo!');
            $this->redirect('Sign:in');
        }
    }

}

?>
