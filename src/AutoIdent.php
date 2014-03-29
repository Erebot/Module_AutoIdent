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

namespace Erebot\Module;

/**
 * \brief
 *      A module which automatically identifies
 *      the bot to a nick server (usually "NickServ").
 */
class AutoIdent extends \Erebot\Module\Base implements \Erebot\Interfaces\HelpEnabled
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
    public function reload($flags)
    {
        if ($flags & self::RELOAD_HANDLERS) {
            $pattern    = $this->parseString('pattern');
            $pattern    = '/'.str_replace('/', '\\/', $pattern).'/i';

            $handler    = new \Erebot\EventHandler(
                \Erebot\CallableWrapper::wrap(array($this, 'handleIdentRequest')),
                new \Erebot\Event\Match\All(
                    new \Erebot\Event\Match\Type(
                        '\\Erebot\\Interfaces\\Event\\Base\\PrivateText',
                        '\\Erebot\\Interfaces\\Event\\Base\\PrivateNotice'
                    ),
                    new \Erebot\Event\Match\TextRegex($pattern)
                )
            );

            $this->connection->addEventHandler($handler);
        }
    }

    /**
     * Provides help about this module.
     *
     * \param Erebot::Interfaces::Event::Base::TextMessage $event
     *      Some help request.
     *
     * \param Erebot::Interfaces::TextWrapper $words
     *      Parameters passed with the request. This is the same
     *      as this module's name when help is requested on the
     *      module itself (in opposition with help on a specific
     *      command provided by the module).
     */
    public function getHelp(
        \Erebot\Interfaces\Event\Base\TextMessage $event,
        \Erebot\Interfaces\TextWrapper $words
    ) {
        if ($event instanceof \Erebot\Interfaces\Event\Base\PrivateMessage) {
            $target = $event->getSource();
            $chan   = null;
        } else {
            $target = $chan = $event->getChan();
        }

        if (count($words) == 1 && $words[0] === get_called_class()) {
            $msg = $this->getFormatter($chan)->_(
                "This module does not provide any command, but ".
                "makes the bot identify to a nickname service (NickServ) ".
                "automatically."
            );
            $this->sendMessage($target, $msg);
            return true;
        }
    }

    /**
     * Handles a request from the nick server for the bot
     * to identify itself.
     *
     * \param Erebot::Interfaces::EventHandler $handler
     *      Handler that triggered this event.
     *
     * \param Erebot::Interfaces::Event::Base::Source $event
     *      The identification request.
     *
     * \return
     *      This method does not return anything.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handleIdentRequest(
        \Erebot\Interfaces\EventHandler $handler,
        \Erebot\Interfaces\Event\Base\Source $event
    ) {
        $nicknames  = explode(' ', $this->parseString('nickserv', 'nickserv'));
        $source     = $event->getSource();
        $found      = false;
        $collator   = $this->connection->getCollator();
        foreach ($nicknames as $nickname) {
            if (!$collator->compare($nickname, $source)) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            return;
        }

        $password = $this->parseString('password');
        $this->sendMessage($source, 'IDENTIFY '.$password);
    }
}
