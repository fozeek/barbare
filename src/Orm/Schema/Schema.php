<?php

namespace Barbare\Framework\Orm\Schema;

use Barbare\Framework\Orm\Schema\Table;

class Schema
{
    private $tables = [];

    public function table($name, $cb)
    {
        $this->tables[$name] = $table = new Table($name);
        $cb($table);
    }

    public function getSql()
    {
        $sql = "";
        foreach ($this->tables as $name => $table) {
            $sql .= $table->getSql().PHP_EOL.PHP_EOL;
        }
        return $sql;
    }
}
/*

CREATE TABLE `coucou` (
    `id` int(11) NOT NULL,
  `tout` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `coucou`
--
ALTER TABLE `coucou`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `coucou`
--
ALTER TABLE `coucou`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

*/