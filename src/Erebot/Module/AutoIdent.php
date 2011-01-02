<?php
/*
    This file is part of Erebot.

    Erebot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Erebot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Erebot.  If not, see <http://www.gnu.org/licenses/>.
*/

class   Erebot_Module_AutoIdent
extends Erebot_Module_Base
{
    protected $_password;

    public function reload($flags)
    {
        if ($flags & self::RELOAD_MEMBERS) {
            $this->_password = $this->parseString('password');
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $pattern    =   $this->parseString('pattern');
            $pattern    =   '/'.str_replace('/', '\\/', $pattern).'/i';

            $filter = new Erebot_Event_Match_All(
                new Erebot_Event_Match_Any(),
                new Erebot_Event_Match_Any(
                    new Erebot_Event_Match_InstanceOf('Erebot_Event_PrivateText'),
                    new Erebot_Event_Match_InstanceOf('Erebot_Event_PrivateNotice')
                ),
                new Erebot_Event_Match_TextRegex($pattern)
            );

            $nicknames  = explode(' ', $this->parseString('nickserv', 'nickserv'));
            foreach ($nicknames as &$nickname) {
                $filter[0]->addFilter(new Erebot_Event_Match_Source($nickname));
            }
            unset($nickname);

            $handler    =   new Erebot_EventHandler(
                array($this, 'handleIdentRequest'),
                $filter
            );

            $this->_connection->addEventHandler($handler);
        }
    }

    public function handleIdentRequest(Erebot_Interface_Event_Source &$event)
    {
        $this->sendMessage($event->getSource(), 'IDENTIFY '.$this->_password);
    }
}

