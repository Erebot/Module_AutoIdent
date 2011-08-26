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

if (!defined('TESTENV_DIR'))
    define(
        'TESTENV_DIR',
        dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testenv'
    );
require_once(TESTENV_DIR . DIRECTORY_SEPARATOR . 'bootstrap.php');

class   AutoIdentTest
extends ErebotModuleTestCase
{
    public function testAutoIdent()
    {
        // Make it look as though the channel
        // uses "password" as its key.
        $this->_serverConfig
            ->expects($this->any())
            ->method('parseString')
            ->will($this->onConsecutiveCalls(
                'nickserv',
                'password'
            ));

        $this->_module = new Erebot_Module_AutoIdent(NULL);
        $this->_module->reload(
            $this->_connection,
            0
        );

        $event = $this->getMock(
            'Erebot_Interface_Event_PrivateText',
            array(), array(), '', FALSE, FALSE
        );
        $event
            ->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->_connection));
        $event
            ->expects($this->any())
            ->method('getText')
            ->will($this->returnValue('foo'));
        // The first time this event is used, an identification request
        // is sent to NickServ. The second time, no such request is sent.
        $event
            ->expects($this->any())
            ->method('getSource')
            ->will($this->onConsecutiveCalls('NickServ', 'NickServFake'));

        $this->_module->handleIdentRequest($this->_eventHandler, $event);
        $this->assertSame(1, count($this->_outputBuffer));
        $output = array_shift($this->_outputBuffer);
        $this->assertSame(
            "PRIVMSG NickServ :IDENTIFY password",
            $output
        );

        // The second request comes from an untrusted nick.
        // Verify that we do not send out the password.
        $this->_module->handleIdentRequest($this->_eventHandler, $event);
        $this->assertSame(0, count($this->_outputBuffer));
        $this->_module->unload();
    }
}

