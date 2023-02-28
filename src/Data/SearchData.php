<?php

namespace App\Data;

class SearchData
{
    /**
     * haine de caractéres à chercher
     * @var string
     */

    private ?string $q = null; 

      /**
     * Numéro de la page acctuel par défault 1
     * @var integer
     */
    private ?int $page = 1;

    private ?int $cat = null;
    //https://www.google.com/search?client=firefox-b-d&q=symfony+filtre+produit#fpstate=ive&vld=cid:e64549d9,vid:4uYpFjfUUbc

    public function getQ(): ?string { return $this->q; }
    public function setQ(?string $q): self { $this->q = $q; return $this; }

    public function getPage(){ return $this->page; }
    public function setPage($page): self { $this->page = $page; return $this; }


    public function getCat(): ?int { return $this->cat; }
    public function setCat(?int $cat): self { $this->cat = $cat; return $this; }
}