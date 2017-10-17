<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


class HomepagePresenter extends Nette\Application\UI\Presenter
{

    private $database;
    private $sort;
    private $smer;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function handleSort($sloupec, $smer)
    {

        $this->sort = $sloupec;
        $this->smer = $smer;

        if ($this->isAjax()) {
            $this->redrawControl('tabulka');
        }
    }

    public function renderDefault()
    {
        if ($this->sort === null) {
            $this->sort = 'id';
            $this->smer = true;
        }
        $this->template->sort = $this->sort;
        $this->template->posts = $this->database->query('SELECT * FROM knihy k JOIN zanry z ON k.zanr_id=z.zan_id ORDER BY', [$this->sort => $this->smer]);
    }

    protected function createComponentCommentForm()
    {

        $zanry = $this->database->fetchPairs('SELECT zan_id, nazev_zanru FROM zanry');;

        $form = new Form; // means Nette\Application\UI\Form

        $form->addText('nazev', 'Název:')->setRequired();

        $form->addText('autor', 'Autor:')->setRequired();

        $form->addSelect('zanr_id', 'Žánr:', $zanry)->setPrompt('Zvolte žánr')->setRequired();

        $form->addSubmit('send', 'Přidat');

        $form->onSuccess[] = [$this, 'commentFormSucceeded'];
        return $form;
    }

    public function commentFormSucceeded($form, $values)
    {

        $this->database->table('knihy')->insert([
            'nazev_knihy' => $values->nazev,
            'autor' => $values->autor,
            'zanr_id' => $values->zanr_id,
        ]);

        $this->flashMessage('Kniha přidána', 'success');
        $this->redirect('this');
    }

}
