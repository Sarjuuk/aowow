var _ = function(family)
{
    family.foodCount = 0;
    for (var food in g_pet_foods)
    {
        if( family.diet & food)
            family.foodCount++;
    }

    family.spellCount = 0;

    for (var i = 0, len = family.spells.length; i < len; ++i)
    {
        if (family.spells[i])
            family.spellCount++;
    }
};
