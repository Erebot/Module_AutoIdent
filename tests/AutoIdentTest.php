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

require_once(
    dirname(__FILE__) .
    DIRECTORY_SEPARATOR . 'testenv' .
    DIRECTORY_SEPARATOR . 'bootstrap.php'
);

class   AutoIdentTest
extends ErebotModuleTestCase
{
    public function testAutoIdent()
    {
        // Make it look as though the channel
        // uses "password" as its key.
        $this->_networkConfig
            ->expects($this->any())
            ->method('parseString')
            ->will($this->onConsecutiveCalls(
                '.*identify.*',
                'nickserv',
                'password'
            ));

        $this->_module = new Erebot_Module_AutoIdent(
            $this->_connection,
            NULL
        );
        $this->_module->reload(Erebot_Module_Base::RELOAD_ALL);

        $event = new Erebot_Event_PrivateText(
            $this->_connection,
            'NickServ', // We also test case-insensitive comparison.
            'foo'   // Does not matter : the module expects bad input
                    // to have already been filtered out by then.
        );
        $this->_module->handleIdentRequest($event);
        $this->assertSame(1, count($this->_outputBuffer));
        $output = array_shift($this->_outputBuffer);
        $this->assertSame(
            "PRIVMSG NickServ :IDENTIFY password",
            $output
        );

        // The second request comes from an untrusted nick.
        // Verify that we do not send out the password.
        $event = new Erebot_Event_PrivateText(
            $this->_connection,
            'NickServFake',
            'foo'   // Does not matter.
        );
        $this->_module->handleIdentRequest($event);
        $this->assertSame(0, count($this->_outputBuffer));
    }
}

