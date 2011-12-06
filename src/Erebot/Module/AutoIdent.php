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
    /**
     * This method is called whenever the module is (re)loaded.
     *
     * \param int $flags
     *      A bitwise OR of the Erebot_Module_Base::RELOAD_*
     *      constants. Your method should take proper actions
     *      depending on the value of those flags.
     *
     * \note
     *      See the documentation on individual RELOAD_*
     *      constants for a list of possible values.
     */
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
     * \param Erebot_Interface_EventHandler $handler
     *      Handler that triggered this event.
     *
     * \param Erebot_Interface_Event_Base_Source $event
     *      The identification request.
     *
     * \return
     *      This method does not return anything.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handleIdentRequest(
        Erebot_Interface_EventHandler       $handler,
        Erebot_Interface_Event_Base_Source  $event
    )
    {
        $nicknames  = explode(' ', $this->parseString('nickserv', 'nickserv'));
        $source     = $event->getSource();
        $found      = FALSE;
        foreach ($nicknames as $nickname) {
            if (!$this->_connection->irccasecmp($nickname, $source)) {
                $found = TRUE;
                break;
            }
        }
        if (!$found)
            return;

        $password = $this->parseString('password');
        $this->sendMessage($source, 'IDENTIFY '.$password);
    }
}

