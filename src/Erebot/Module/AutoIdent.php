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

/**
 * \brief
 *      A module which automatically identifies
 *      the bot to a nick server (usually "NickServ").
 */
class   Erebot_Module_AutoIdent
extends Erebot_Module_Base
{
    /// \copydoc Erebot_Module_Base::_reload()
    public function _reload($flags)
    {
        if ($flags & self::RELOAD_HANDLERS) {
            $pattern    =   $this->parseString('pattern');
            $pattern    =   '/'.str_replace('/', '\\/', $pattern).'/i';

            $handler    =   new Erebot_EventHandler(
                new Erebot_Callable(array($this, 'handleIdentRequest')),
                new Erebot_Event_Match_All(
                    new Erebot_Event_Match_Any(
                        new Erebot_Event_Match_InstanceOf(
                            'Erebot_Interface_Event_Base_PrivateText'
                        ),
                        new Erebot_Event_Match_InstanceOf(
                            'Erebot_Interface_Event_Base_PrivateNotice'
                        )
                    ),
                    new Erebot_Event_Match_TextRegex($pattern)
                )
            );

            $this->_connection->addEventHandler($handler);
        }
    }

    /// \copydoc Erebot_Module_Base::_unload()
    protected function _unload()
    {
    }

    /**
     * Handles a request from the nick server for the bot
     * to identify itself.
     *
     * \param Erebot_Interface_Event_Base_Source $event
     *      The identification request.
     *
     * \return
     *      This method does not return anything.
     */
    public function handleIdentRequest(Erebot_Interface_Event_Base_Source $event)
    {
        $nicknames  = explode(' ', $this->parseString('nickserv', 'nickserv'));
        $found      = FALSE;
        foreach ($nicknames as $nickname) {
            if (!$this->_connection->irccasecmp(
                $nickname, $event->getSource())) {
                $found = TRUE;
                break;
            }
        }
        if (!$found)
            return;

        $password = $this->parseString('password');
        $this->sendMessage($event->getSource(), 'IDENTIFY '.$password);
    }
}

